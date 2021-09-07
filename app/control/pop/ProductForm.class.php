<?php
/**
 * Product Form
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ProductForm extends TPage
{
    protected $form;
    
    // trait with saveFile, saveFiles, ...
    use Adianti\Base\AdiantiFileSaveTrait;
    
    function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Product');
        $this->form->setFormTitle('Cadastro de produto');
        $this->form->setClientValidation(true);
        
        // create the form fields
        $id          = new TEntry('id');
        $description = new TEntry('description');
        $nome = new TEntry('nome');
        $stock       = new TEntry('stock');
        $sale_price  = new TEntry('sale_price');
        $unity       = new TCombo('unity');
        $photo_path  = new TFile('photo_path');
       // $images      = new TMultiFile('images');
        
        // allow just these extensions
        $photo_path->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
       // $images->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
        
        // enable progress bar, preview
        $photo_path->enableFileHandling();
        $photo_path->enablePopover();
        
        // enable progress bar, preview, and gallery mode
       // $images->enableFileHandling();
       // $images->enableImageGallery();
       // $images->enablePopover('Preview', '<img style="max-width:300px" src="download.php?file={file_name}">');
        
        $id->setEditable( FALSE );
        $unity->addItems( ['pizza' => 'Pizzas', 'hamburguer' => 'Hamburguers', 'esfihas' => 'Esfihas', 'cerveja' => 'Cervejas', 'refrigerante' => 'Refrigerantes',  'bebidas' => 'Outras bebidas', 'sucos' => 'Sucos', 'milk' => 'Milk Shakes', 'adicional' => 'Adicionais', 'petisco' => 'Petiscos', 'outros' => 'Outros'] );
        $stock->setNumericMask(2, ',', '.', TRUE); // TRUE: process mask when editing and saving
        $sale_price->setNumericMask(2, ',', '.', TRUE); // TRUE: process mask when editing and saving
        
        // add the form fields
        $this->form->addFields( [new TLabel('ID', 'red')],          [$id] );
        $this->form->addFields( [new TLabel('Nome', 'red')], [$nome] );
        $this->form->addFields( [new TLabel('Descrição', 'red')], [$description] );
        $this->form->addFields( [new TLabel('Estoque', 'red')],       [$stock],
                                [new TLabel('Preço', 'red')],  [$sale_price] );
        $this->form->addFields( [new TLabel('Categoria', 'red')],       [$unity] );
        $this->form->addFields( [new TLabel('Foto do produto')],  [$photo_path] );
       // $this->form->addFields( [new TLabel('Imagens')],  [$images] );
        
        $id->setSize('50%');
        
        $description->addValidation('Description', new TRequiredValidator);
        $stock->addValidation('Stock', new TRequiredValidator);
        $sale_price->addValidation('Sale Price', new TRequiredValidator);
        $unity->addValidation('Unity', new TRequiredValidator);
        
        // add the actions
        $this->form->addAction( 'salvar', new TAction([$this, 'onSave']), 'fa:save green');
        $this->form->addActionLink( 'Limpar', new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addActionLink( 'Listar produtos', new TAction(['ProductList', 'onReload']), 'fa:table blue');

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'ProductList'));
        $vbox->add($this->form);

        parent::add($vbox);
    }
    
    /**
     * Overloaded method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            TTransaction::open('samples');
            
            // form validations
            $this->form->validate();
            
            // get form data
            $data   = $this->form->getData();
            
            // store product
            $object = new Product;
            $object->fromArray( (array) $data);
            $object->store();
            
            // copy file to target folder
            $this->saveFile($object, $data, 'photo_path', 'files/images');
            
            $this->saveFiles($object, $data, 'images', 'files/images', 'ProductImage', 'image', 'product_id');
            
            // send id back to the form
            $data->id = $object->id;
            $this->form->setData($data);
            
            TTransaction::close();
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e)
        {
            $this->form->setData($this->form->getData());
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                TTransaction::open('samples');
                $object = new Product( $param['key'] );
                $object->images = ProductImage::where('product_id', '=', $param['key'])->getIndexedArray('id', 'image');
                $this->form->setData($object);
                TTransaction::close();
                return $object;
            }
            else
            {
                $this->form->clear();
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}
