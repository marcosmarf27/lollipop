<?php


use Adianti\Control\TPage;
use Adianti\Control\TAction;
use Adianti\Widget\Form\TDate;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Dialog\TAlert;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Template\THtmlRenderer;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Wrapper\BootstrapDatagridWrapper;
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
class DeliveryAdmin extends TPage
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
        $who = 'World';
 
        $json = DeliveryService::buscarPedidosGloriaFood();
        DeliveryService::updateDelivery();
        
        $this->setDatabase('samples');          // defines the database
        $this->setActiveRecord('PedidoDelivery');         // defines the active record
        $this->setDefaultOrder('gloria_food_id', 'desc');    // defines the default order
        $this->addFilterField('gloria_food_id', '=', 'id'); // filterField, operator, formField
       // $this->addFilterField('customer_id', '=', 'customer_id'); // filterField, operator, formField
        
        $this->addFilterField('data_pedido', '>=', 'date_from', function($value) {

          
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        $this->addFilterField('data_pedido', '<=', 'date_to', function($value) {

          
            return TDate::convertToMask($value, 'dd/mm/yyyy', 'yyyy-mm-dd');
        }); // filterField, operator, formField, transformFunction
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Sale');
        $this->form->setFormTitle('Pedidos do Sistema Delivery');
        
        // create the form fields
        $id        = new TEntry('id');
        $date_from = new TDate('date_from');
        $date_to   = new TDate('date_to');
        
      //  $customer_id = new TDBUniqueSearch('customer_id', 'samples', 'Customer', 'id', 'name');
       // $customer_id->setMinLength(1);
       // $customer_id->setMask('{name} ({id})');
        
        // add the fields
        $this->form->addFields( [new TLabel('N?? do Pedido')],          [$id]); 
         $this->form->addFields( [new TLabel('Data (de)')], [$date_from],
                                [new TLabel('Data (At??)')],   [$date_to] ); 
        //$this->form->addFields( [new TLabel('Customer')],    [$customer_id] );
        
        $id->setSize('50%');
        $date_from->setSize('100%');
        $date_to->setSize('100%');
        $date_from->setMask( 'dd/mm/yyyy' );
        $date_to->setMask( 'dd/mm/yyyy' );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('SaleList_filter_data') );
        
        // add the search form actions
        $this->form->addAction('Buscar', new TAction([$this, 'onSearch']), 'fa:search');
       // $this->form->addActionLink('New',  new TAction(['SaleForm', 'onEdit']), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->width = '100%';
        $this->datagrid->datatable = 'true'; 
        $this->datagrid->disableDefaultClick();
        
        // creates the datagrid columns
        $column_id       = new TDataGridColumn('gloria_food_id', 'N??', 'center', '10%');
        $column_date     = new TDataGridColumn('data_pedido', 'Data', 'center', '20%');
        $column_pag     = new TDataGridColumn('used_payment_methods', 'Pagamento', 'center', '30%');
        $column_customer = new TDataGridColumn('client_first_name', 'Cliente', 'left', '20%');
        $column_total    = new TDataGridColumn('total_price', 'Total', 'right', '15%');
        $column_whatsapp   = new TDataGridColumn('client_phone', 'Whatsapp', 'right', '15%');

        $column_customer->setDataProperty('style','font-weight: bold');
        $column_id->setDataProperty('style','font-weight: bold');
        // define format function
        $format_value = function($value) {
            if (is_numeric($value)) {
                return 'R$ '.number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
        $column_total->setTransformer( $format_value );

        $column_whatsapp->setTransformer( function ($value, $object, $row) {
            if ($value)
            {
                $value = str_replace([' ','-','(',')'],['','','',''], $value);
                $icon  = "<i class='fab fa-whatsapp' aria-hidden='true'></i>";


             TTransaction::open('samples');

        
             
              
                $html = new THtmlRenderer('app/resources/modelo.txt');
                $html->disableHtmlConversion();

                $replace = array();
                $replace['gloria_food_id']    =  $object->gloria_food_id;
              
                $replace['accepted_at']    =  $object->accepted_at;
                $replace['used_payment_methods']    =  $object->used_payment_methods ;
                $replace['client_first_name']    =  $object->client_first_name;
                $replace['client_last_name']    =  $object->client_last_name;
                $replace['client_address']    =  $object->client_address;
                $replace['client_phone']    =  $object->client_phone;
                $replace['client_email']    =  $object->client_email;
                $replace['total_price']    =  $object->total_price;

            $html->enableSection('main', $replace);
        
            
            // replace the main section variables
           
            
            // define the replacements based on customer contacts

            $itens = ItemDelivery::where('pedido_delivery_id', '=', $object->id)->load();
            $replace = array();
     
            foreach($itens as $item){


                $jsonitem = json_decode($item->opcoes);
                $opformatada = '';
                foreach($jsonitem as $ob){

                    $opformatada .= strtolower( $ob->group_name . ': '. $ob->name . PHP_EOL);

                }
                $replace[] = array('item' => $item->name,
                                   'opcao'=> $opformatada);

            }
                

              
        
            
            // define with sections will be enabled
     
            $html->enableSection('itens', $replace, TRUE);


            $msg = $html->getContents();
$msg2 = urlencode($msg);

TTransaction::close();
      
                return "{$icon} <a target='newwindow' href='https://api.whatsapp.com/send?phone={$value}&text={$msg2}'> {$value} </a>";
            }
            return $value;
        });
        
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_date);
        $this->datagrid->addColumn($column_customer);
        $this->datagrid->addColumn($column_whatsapp);
        $this->datagrid->addColumn($column_total);
        $this->datagrid->addColumn($column_pag);
        
        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']),   ['order' => 'gloria_food_id']);
        $column_date->setAction(new TAction([$this, 'onReload']), ['order' => 'accepted_at']);
        
        // define the transformer method over date
        $column_date->setTransformer( function($value, $object, $row) {

            $data_parte1 = explode('T', $value );
            $date = new DateTime($data_parte1[0]);
            return $date->format('d/m/Y');
        });
        $action_view   = new TDataGridAction(['CustomerStatusViewDelivery', 'onCheckStatus'],   ['key' => '{id}'] );
      //  $action_edit   = new TDataGridAction(['SaleForm', 'onEdit'],   ['key' => '{id}'] );
      //  $action_delete = new TDataGridAction([$this, 'onDelete'],   ['key' => '{id}'] );
        
        $this->datagrid->addAction($action_view, 'Ver detalhes', 'fa:search green fa-fw');
       // $this->datagrid->addAction($action_edit, 'Edit',   'far:edit blue fa-fw');
       // $this->datagrid->addAction($action_delete, 'Delete', 'far:trash-alt red fa-fw');
       $column_total->setTotalFunction( function($column_total) {
        return array_sum((array) $column_total);
    });

    $column_pag->setTransformer(function($value) {
            
               
                
        switch($value)
        {

            case 'CASH':
               
               
                $div = new TElement('span');
                $div->class="label label-primary";
                $div->style="text-shadow:none; font-size:12px";
                $div->add('Dinheiro');
                return " $div";
                break;
            case 'Transferencia Bancaria':
               
              
                $div = new TElement('span');
                $div->class="label label-success";
                $div->style="text-shadow:none; font-size:12px";
                $div->add('Pix');
                return " $div";
                break;
            case 'CARD':
              
               
               
                $div = new TElement('span');
                $div->class="label label-info";
                $div->style="text-shadow:none; font-size:12px";
                $div->add('Cart??o');
                return " $div";
                break;

            case 'Dinheiro (eu preciso de troco)':
            
            
            
                $div = new TElement('span');
                $div->class="label label-warning";
                $div->style="text-shadow:none; font-size:12px";
                $div->add('Dinheiro com Troco');
                return " $div";
                break;
          
            


        }
     
     });
        // create the datagrid model
        $this->datagrid->createModel();
        
      $json = DeliveryService::buscarPedidosGloriaFood();

      DeliveryService::updateDelivery();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->enableCounters();
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
      //  $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        parent::add(new TAlert('success', '(SISTEMA DELIVERY) Novos pedidos  recebidos com sucesso! : ' . $json->count ));
        $container->add($panel = TPanelGroup::pack('', $this->datagrid, $this->pageNavigation));
        $panel->addHeaderActionLink( 'PDF', new TAction([$this, 'onExportPDF'], ['register_state' => 'false']), 'far:file-pdf red' );
        $panel->getBody()->style = 'overflow-x:auto';
        parent::add($container);
    }

  
}