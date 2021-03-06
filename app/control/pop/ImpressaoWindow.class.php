<?php
/**
 * CustomerFormWindow
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class ImpressaoWindow extends TWindow
{
    public function __construct($param)
    {
        parent::__construct($param);
        parent::setSize(0.5, null);
        parent::setMinWidth(0.5, 700);
        parent::removePadding();
        parent::disableEscape();
        parent::setTitle('Impressao');
        
        $this->form = new SaleSidePanelView($param, true);
        $this->form->setTargetContainer('');
        parent::add($this->form);
    }
    
    /**
     * Redirect calls to decorated object
     */
    public function onPrint($param)
    {
        $this->form->onPrint($param);
        TWindow::closeWindowByName('SaleForm');
        TWindow::closeWindowByName('ImpressaoWindow');
    }
}