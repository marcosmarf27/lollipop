<?php
/**
 * FormImageUploader
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class FormImageUploader extends TPage
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
        $this->form->setFormTitle( _t('Image upload and cropper') );
        
        // create the form fields
        $imagecropper = new TImageCropper('imagecropper');
        $imageupload  = new TFile('imageupload');
        $imagecapture = new TImageCapture('imagecapture');
        
        $imagecropper->setSize(300, 150);
        $imagecropper->setCropSize(300, 150);
        $imagecropper->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
        //$imagecropper->enableFileHandling();
        
        $imageupload->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
        //$imageupload->enableFileHandling();
        $imageupload->enableImageGallery();
        
        $imagecapture->setAllowedExtensions( ['gif', 'png', 'jpg', 'jpeg'] );
        $imagecapture->setSize(300, 200);
        $imagecapture->setCropSize(300, 200);
        
        $this->form->addFields( [new TLabel('Image Cropper')], [$imagecropper] );
        $this->form->addFields( [new TLabel('Image Uploader')],[$imageupload] );
        $this->form->addFields( [new TLabel('Image Capture')], [$imagecapture] );
        
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
    public static function onShow($param)
    {
        // show the message
        new TMessage('info', '<b>Image Crop</b>: tmp/'. $param['imagecropper'] . '<br>'.
                            ' <b>Image Upload</b>: tmp/' . $param['imageupload'] . '<br>'.
                            ' <b>Image Capture</b>: tmp/' . $param['imagecapture'] );
    }
}
