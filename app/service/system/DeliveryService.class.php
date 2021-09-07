<?php

use Adianti\Database\TConnection;
use Adianti\Database\TTransaction;

/**
 * Database Information Service
 *
 * @version    3.0
 * @package    service
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006-2014 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class DeliveryService
{
   
    
    /**
     * Get database tables
     */
    public static function updateDelivery()
    {
      TTransaction::open('samples');

      $pedidos = JsonDelivery::where('salvo', '=', 'nao')->load();

   

      if($pedidos){

        foreach($pedidos as $remessa){

         

            if($remessa->quantidade > 0){
                $remessa_json = json_decode($remessa->json_pedido);

                foreach($remessa_json->orders as $pedido_individual ){

                    
            
       

                $pedido_banco = new PedidoDelivery();
                $pedido_banco->client_first_name = $pedido_individual->client_first_name;
                $pedido_banco->client_last_name = $pedido_individual->client_last_name;
                $pedido_banco->client_email = $pedido_individual->client_email;
                $pedido_banco->client_phone = $pedido_individual->client_phone;
                $pedido_banco->status = $pedido_individual->status;
                $pedido_banco->accepted_at = $pedido_individual->accepted_at;
                $pedido_banco->used_payment_methods =  $pedido_individual->used_payment_methods[0];

                $pedido_banco->latitude = $pedido_individual->latitude;
                $pedido_banco->longitude = $pedido_individual->longitude;
                $pedido_banco->instructions = $pedido_individual->instructions;
                $pedido_banco->client_address = $pedido_individual->client_address;
                $pedido_banco->total_price = $pedido_individual->total_price;
                $pedido_banco->accepted_at = $pedido_individual->accepted_at;
                $pedido_banco->gloria_food_id = $pedido_individual->id;

                $data_divida = explode('T', $pedido_individual->accepted_at);
                $mes_ano = date_parse($data_divida[0]);

                $pedido_banco->ano = $mes_ano['year'];
                $pedido_banco->mes = $mes_ano['month'];
                $pedido_banco->hora =$data_divida[1];
                $pedido_banco->data_pedido = $data_divida[0];
                $pedido_banco->store();


                foreach($pedido_individual->items as $item_pedido_individual){

                  

               

                    $item_banco = new ItemDelivery();
                    $item_banco->name = $item_pedido_individual->name;
                    $item_banco->total_item_price = $item_pedido_individual->total_item_price;
                    $item_banco->quantity = $item_pedido_individual->quantity;
                    $item_banco->gloria_item_id = $item_pedido_individual->id;
                    $item_banco->obs = $item_pedido_individual->instructions;
                   // $item_banco->coupon = $item_pedido_individual->coupon ? $item_pedido_individual->coupon : '';
                    $item_banco->item_discount = $item_pedido_individual->item_discount;
                    $item_banco->opcoes = json_encode($item_pedido_individual->options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ;



                    $item_banco->pedido_delivery_id = $pedido_banco->id;
                    $item_banco->store();

                    $salvar_remessa =  new JsonDelivery($remessa->id);
                    $salvar_remessa->salvo = 'sim';
                    $salvar_remessa->store();




                }



                }

              
            }

            

        
        }
      }
      
      

      TTransaction::close();
    }

    public static function buscarPedidosGloriaFood(){

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://pos.globalfoodsoft.com/pos/order/pop',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
          'Authorization: l8l3ntBaQI45qdRv9N'
        ),
      ));
      
      $response = curl_exec($curl);

      if($response){
          TTransaction::open('samples');

          $json = json_decode($response);

          if(!$json->count == '0'){

              $dados_json = new JsonDelivery();
              $dados_json->data_json =  Date('Y-m-d h:i:s');
              $dados_json->json_pedido = $response;
  
             
              $dados_json->quantidade =  $json->count;
              $dados_json->salvo = 'nao';
              $dados_json->store();
  

          }

       

          TTransaction::close();
return $json;
      }
      
      curl_close($curl);
    }
    
    /**
     * Get list of database connections
     */
   
}