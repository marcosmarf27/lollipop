<?php


/**
 * SaleSidePanelView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SaleSidePanelView extends TPage
{
    protected $form; // form
    protected $detail_list;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        
        $this->form = new BootstrapFormBuilder('form_Sale_View');
        
        $this->form->setFormTitle('Detalhes do pedido nº ' . $param['key']);
        $this->form->setColumnClasses(2, ['col-sm-2', 'col-sm-10']);
        $iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
$ipad = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
$android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
$palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
$berry = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
$ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
$symbian =  strpos($_SERVER['HTTP_USER_AGENT'],"Symbian");

if ($iphone || $ipad || $android || $palmpre || $ipod || $berry || $symbian == true) {

    $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
  
} else {
    $this->form->addHeaderActionLink( _t('Print'), new TAction([$this, 'onPrint'], ['key'=>$param['key'], 'static' => '1']), 'far:file-pdf red');
    //  $this->form->addHeaderActionLink( _t('Edit'), new TAction([$this, 'onEdit'], ['key'=>$param['key']]), 'far:edit red');
      $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
}

      





    
        
        parent::add($this->form);
    }
    
    /**
     * Load content
     */
    public function onView($param)
    {
        try
        {
            TTransaction::open('samples');
            
            $master_object = new Sale($param['key']);


            switch($master_object->pagamento)
            {

                case 1:
                 $forma = 'Dinheiro';
                break;
                case 2:
                    $forma = 'Debito';
                    break;
                case 3:
                    $forma = 'Credito';
                    break;
                case 4:

                    $forma = 'Pix';
                
                    break;

             default:

             $forma = "Nao selecionado";
            
                


            }
            
            $label_id = new TLabel('Nº do pedido:', '#333333', '14px', 'b');
            $label_date = new TLabel('Data:', '#333333', '14px', 'b');
            $label_total = new TLabel('Total:', '#333333', '14px', 'b');
            $label_customer_id = new TLabel('Cliente:', '#333333', '14px', 'b');
            $label_obs = new TLabel('Obs:', '#333333', '14px', 'b');
            $label_pagamento = new TLabel('Pagamento:', '#333333', '14px', 'b');
            $label_end_cliente = new TLabel('Endereço:', '#333333', '14px', 'b');
    
            $text_id  = new TTextDisplay($master_object->id, '#333333', '14px', '');
            $text_date  = new TTextDisplay($master_object->date, '#333333', '14px', '');
            $text_total  = new TTextDisplay($master_object->total, '#333333', '14px', '');
            $text_customer_id  = new TTextDisplay(strtoupper(Customer::find($master_object->customer_id)->name), '#333333', '12px', '');
            $text_obs  = new TTextDisplay(strtoupper($master_object->obs), '#333333', '14px', '');
            $text_end_cliente = new TTextDisplay($master_object->customer->address, '#333333', '14px', '');
            $text_pagamento = new TTextDisplay(strtoupper($forma), '#333333', '14px', '');
            $text_end  = new TTextDisplay('<center> Lollipop Pizzaria </center> <br> Rua Monsenhor João Luís, n°255 - Centro, Palhano - CE, 62910-000', '#333333', '12px', '');
    
           // $this->form->addFields([$label_id],[$text_id]);
           // $this->form->addFields([$label_date],[$text_date]);
           // $this->form->addFields([$label_total],[$text_total]);
            $this->form->addFields([$label_customer_id],[$text_customer_id]);
            $this->form->addFields([$label_pagamento],[$text_pagamento]);
            $this->form->addFields([$label_end_cliente], [$text_end_cliente]);
            $this->form->addFields([$label_obs],[$text_obs]);

 


            
            $this->detail_list = new BootstrapDatagridWrapper( new TDataGrid );
            $this->detail_list->style = 'width:100%;';
            $this->detail_list->disableDefaultClick();
            
            $product       = new TDataGridColumn('product->description',  'Descrição', 'left');
            $price         = new TDataGridColumn('sale_price',  'Preço',    'right');
            $amount        = new TDataGridColumn('amount',  'qtd',    'center');
           // $discount      = new TDataGridColumn('discount',  'Desconto',    'right');
            $total         = new TDataGridColumn('total',  'Total',    'right');
            
            $this->detail_list->addColumn( $product );
            $this->detail_list->addColumn( $price );
            $this->detail_list->addColumn( $amount );
          //  $this->detail_list->addColumn( $discount );
            $this->detail_list->addColumn( $total );
            
            $format_value = function($value) {
                if (is_numeric($value)) {
                    return 'R$ '.number_format($value, 2, ',', '.');
                }
                return $value;
            };
            
            $total->setTransformer($format_value);
            
            // define totals
            $total->setTotalFunction( function($values) {
                return array_sum((array) $values);
            });
            
            $this->detail_list->createModel();
            
            $items = SaleItem::where('sale_id', '=', $master_object->id)->load();
            $this->detail_list->addItems($items);
            
            $panel = new TPanelGroup();
            $panel->style = 'margin-left:-29px; margin-right:-29px;';
            $panel->add($this->detail_list);
            $panel->getBody()->style = 'overflow-x:auto; margin-left:-18px; margin-right:-18px;';
            
            $this->form->addContent([$panel]);
            $this->form->addFields([$text_end]);
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public function onPrint($param)
    {
        try
        {
            $this->onView($param);
            
            // string with HTML contents
            $html = clone $this->form;
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            
            $options = new \Dompdf\Options();
            $options->setChroot(getcwd());
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($contents);
            $customPaper = array(0,0,280,560);
            $dompdf->setPaper($customPaper, 'portrait');
            $dompdf->render();
            
            $file = 'app/output/sale-export.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Pedido', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file.'?rndval='.uniqid();
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * onEdit
     */
    public static function onEdit($param)
    {
        unset($param['static']);
        AdiantiCoreApplication::loadPage('SaleForm', 'onEdit', $param);
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}


