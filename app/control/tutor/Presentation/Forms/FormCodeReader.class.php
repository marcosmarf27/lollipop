<?php
/**
 * FormCodeReader
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class FormCodeReader extends TPage
{
    private $form;
    
    /**
     * Page constructor
     */
    function __construct()
    {
        parent::__construct();
        
        // create the form
        $this->form = new BootstrapFormBuilder;
        $this->form->setFormTitle( _t('Barcode, QRCode reader') );
        
        // create the form fields
        $barcode = new TBarCodeInputReader('barcode');
        $qrcode  = new TQRCodeInputReader('qrcode');
        
        $barcode->setSize( '100%');
        $qrcode->setSize( '100%');
        
        $barcode->setChangeAction( new TAction( [$this, 'onChange'] ) );
        $qrcode->setChangeAction( new TAction( [$this, 'onChange'] ) );
        
        $this->form->addFields( [new TLabel('Barcode')], [$barcode, new TLabel('Only supported in HTTPS mode', 'gray')] );
        $this->form->addFields( [new TLabel('QRCode')],  [$qrcode,  new TLabel('Only supported in HTTPS mode', 'gray')] );
        
        $this->form->addAction('Show', new TAction(array($this, 'onShow')), 'far:check-circle green');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        parent::add($vbox);
    }
    
    /**
     *
     */
    public static function onChange($param)
    {
        new TMessage('info', '<b>onChange</b><br>'.str_replace(',', '<br>', json_encode($param)));
    }
    
    /**
     * Show the form content
     */
    public function onShow($param)
    {
        $data = $this->form->getData();
        $this->form->setData($data); // put the data back to the form
        
        // show the message
        new TMessage('info', '<b>Barcode</b>: '. $data->barcode. ' <br> <b>QRCode</b>: ' . $data->qrcode);
    }
}
