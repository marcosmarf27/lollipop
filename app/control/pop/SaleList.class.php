<?php
/**
 * SaleList
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SaleList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    
    use Adianti\Base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('samples');          // defines the database
        $this->setActiveRecord('Sale');         // defines the active record
        $this->setDefaultOrder('id', 'desc');    // defines the default order
        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('customer_id', '=', 'customer_id'); // filterField, operator, formField
        $this->addFilterField('status', '=', 'status');
        
        $this->addFilterField('date', '>=', 'date_from', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        $this->addFilterField('date', '<=', 'date_to', function($value) {
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction

        $criteria = new TCriteria;
        $dataatual = new DateTime('now');
       // $criteria->add(new TFilter('date', '=',  date('Y-m-d')), TExpression::OR_OPERATOR); 
        $criteria->add(new TFilter('date', '>=', $dataatual->format('Y-m-d')), TExpression::OR_OPERATOR); 
       // $criteria->add(new TFilter('date', '>=', $dataatual->format('Y-m-d')), TExpression::OR_OPERATOR); 

        $this->setCriteria($criteria);
 
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Sale');
        $this->form->setFormTitle('Lista de Pedidos');
        
        // create the form fields
        $status  =  new TCombo('status');
        $status_itens = ['1'=>'Aberto', '2'=>'Pago', '3'=>'Cancelado'];
        $status->addItems($status_itens);
        $id        = new TEntry('id');
        $date_from = new TDate('date_from');
        $date_to   = new TDate('date_to');
        
        $customer_id = new TDBUniqueSearch('customer_id', 'samples', 'Customer', 'id', 'name');
        $customer_id->setMinLength(1);
        $customer_id->setMask('{name} ({id})');
        
        // add the fields
        $this->form->addFields( [new TLabel('Nº pedido')],          [$id], [new TLabel('Status')],          [$status]); 
        $this->form->addFields( [new TLabel('Data (de)')], [$date_from],
                                [new TLabel('Data (à)')],   [$date_to] );
        $this->form->addFields( [new TLabel('Cliente ou Mesa')],    [$customer_id] );
        
        $id->setSize('100%');
        $status->setSize('100%');
        $date_from->setSize('100%');
        $date_to->setSize('100%');
        $customer_id->setSize('100%');
        $date_from->setMask( 'dd/mm/yyyy' );
        $date_to->setMask( 'dd/mm/yyyy' );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('SaleList_filter_data') );
        
        // add the search form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addActionLink('Novo pedido',  new TAction(['SaleForm', 'onEdit']), 'fa:plus green');

        //$this->form->addExpandButton();
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->datatable = 'true';
        
        // creates the datagrid columns
        $column_id       = new TDataGridColumn('id', 'Nº pedido', 'center', '10%');
        $column_date     = new TDataGridColumn('date', 'Data', 'center', '20%');
        $column_customer = new TDataGridColumn('customer->name', 'Cliente', 'left', '30%');
        $column_pagamento    = new TDataGridColumn('pagamento', 'Pago em...', 'right', '20%');
        $column_status    = new TDataGridColumn('status', 'Situação', 'right', '10%');
        $column_total    = new TDataGridColumn('total', 'Total', 'right', '10%');
        
        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $column_total->setTransformer( $format_value );
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_status);
        $this->datagrid->addColumn($column_pagamento);
        $this->datagrid->addColumn($column_date);
        $this->datagrid->addColumn($column_customer);
        $this->datagrid->addColumn($column_total);
        
        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'id']);
        $column_date->setAction(new TAction([$this, 'onReload']), ['order' => 'date']);
        
        // define the transformer method over date
        $column_date->setTransformer( function($value, $object, $row) {
            $date = new DateTime($value);
            return $date->format('d/m/Y');
        });

        $action_view   = new TDataGridAction(['SaleSidePanelView', 'onView'],   ['key' => '{id}', 'register_state' => 'false'] );
        $action_edit   = new TDataGridAction(['SaleForm', 'onEdit'],   ['key' => '{id}'] );
        $action_edit->setUseButton(TRUE);
      //  $action_delete = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );
        
        $this->datagrid->addAction($action_view, 'Ver detalhes', 'fa:search green fa-fw');
        $this->datagrid->addAction($action_edit, 'Editar',   'far:edit blue fa-fw');
       // $this->datagrid->addAction($action_delete, 'Deletar', 'far:trash-alt red fa-fw');


        $column_pagamento->setTransformer(function($value) {
            
               
                
            switch($value)
            {

                case 1:
                    $icon  = "<i class='fas fa-money-bill-wave' title = 'Pagamento em dinheiro' aria-hidden='true'></i>";
                  
                   
                    $div = new TElement('span');
                   
                

                    
                    return "{$icon} Dinheiro";
                    break;
                case 2:
                    $icon  = "<i class='fas fa-credit-card' title = 'Pagamento em cartão de crédito'  aria-hidden='true'></i>";
                  
                   
                   
                    return "{$icon} Cartão de crédito";
                    break;
                case 3:
                    $icon  = "<i class='fas fa-tablet-alt' title = 'Pagamento em pix'  aria-hidden='true'></i>";

                 
                   
                   
                
                    return "{$icon} Pix";
                    break;
                case 4:
                    $icon  = "<i class='fas fa-tablet-alt' title = 'Pagamento em pix'  aria-hidden='true'></i>";
              
                    
                    
                   
                
                    return "{$icon} Pix";
                    break;

             
            
                


            }
         
         });

         $column_status->setTransformer(function($value) {
            
               
                
            switch($value)
            {

                case 1:
                   
                   
                    $div = new TElement('span');
                    $div->class="label label-primary";
                    $div->style="text-shadow:none; font-size:12px";
                    $div->add('Aberto');
                    return " $div";
                    break;
                case 2:
                   
                  
                    $div = new TElement('span');
                    $div->class="label label-success";
                    $div->style="text-shadow:none; font-size:12px";
                    $div->add('Pago');
                    return " $div";
                    break;
                case 3:
                  
                   
                   
                    $div = new TElement('span');
                    $div->class="label label-danger";
                    $div->style="text-shadow:none; font-size:12px";
                    $div->add('Cancelado');
                    return " $div";
                    break;
              
                


            }
         
         });

         $column_total->setTotalFunction( function($column_total) {
            return array_sum((array) $column_total);
        });
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->getBody()->style = 'overflow-x:auto';
        $panel->addHeaderActionLink( 'PDF', new TAction([$this, 'onExportPDF'], ['register_state' => 'false']), 'far:file-pdf red' );



        //busca pedidos novos e salva na bse de dados parea delivery admin usar

      $json = DeliveryService::buscarPedidosGloriaFood();

        DeliveryService::updateDelivery();
        parent::add(new TAlert('success', '(SISTEMA DELIVERY) Novos pedidos  recebidos com sucesso! : ' . $json->count ));
        parent::add($container);
    }
   
    
}
