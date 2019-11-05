<?php

namespace Apps\OutOfStockManager;

use Sellfino\DB;
use Sellfino\Helpers;
use Sellfino\Shopify;
use Liquid\Template;

Class App
{

  public function install()
  {

    Shopify::hook('inventory_levels/update', 'OutOfStockManager/webhook/inventory_levels/update', 'OutOfStockManager');

  }

  public function uninstall()
  {

    Shopify::unhook('OutOfStockManager/webhook/inventory_levels/update', 'OutOfStockManager');
    
  }

  public static function info()
  {

    $info = json_decode(file_get_contents(__DIR__ . '/info.json'), true);
    return $info;

  }

  public function router($route)
  {

    switch ($route) {

      case 'products':
        $this->products();
        break;

      case 'settings':
        $this->settings();
        break;

      case 'settings/update':
        $this->settingsUpdate();
        break;

      case 'add':
        $this->add();
        break;

      case 'webhook/inventory_levels/update':
        $this->webhook();
        break;

    }
    
  }

  public function products()
  {

    $items = DB::get('products', 'OutOfStockManager.json');
    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

    if ($search != '') {
      $items = array_filter($items, function($item) use($search) {
        return strpos(strtolower($item['product_name']), strtolower($search)) !== false;
      });
    }

    $items = array_reverse($items);
    $cut = array_slice($items, $page * 50 - 50, $page * 50);

    Helpers::json($cut);

  }

  public function settings()
  {

    $settings = DB::get('settings', 'OutOfStockManager.json');

    if (!isset($settings['subject'])) {

      $settings['subject'] = '{{product.title | strip_html}} is now available to order from {{shop.name}}';

    }

    if (!isset($settings['body'])) {

      $settings['body'] = "You asked us to tell you when <strong>{{product.title}}</strong> would be available to purchase. \n\nWe are pleased to tell you it is now available.\n\nClick below to open the product's page.";

    }

    Helpers::json($settings);

  }

  public function settingsUpdate()
  {

    $data = json_decode(file_get_contents('php://input'), true);
    DB::put('settings', $data, 'OutOfStockManager.json');

    Helpers::success();

  }

  public function webhook()
  {

    $data = json_decode(file_get_contents('php://input'), true);

    if ($data['available'] > 0) {

      $items = DB::get('products', 'OutOfStockManager.json');

      if (isset($items['inventory_id_' . $data['inventory_item_id']])) {

        $item = $items['inventory_id_' . $data['inventory_item_id']];
        $tmpl = new Template();

        if (file_exists(DIR_STORES . $_SESSION['shop'] . '/OutOfStockManager/emails/notification.html')) {

          $html = file_get_contents(DIR_STORES . $_SESSION['shop'] . '/OutOfStockManager/emails/notification.html');

        } else {

          $html = file_get_contents(__DIR__ . '/emails/notification.html');

        }

        $tmpl->parse($html);

        $data_product = json_decode(Shopify::request('products/' . $item['product']['id'] . '.json'), true);

        if (isset($data_product['errors'])) {
          return;
        }

        $data_shop = json_decode(Shopify::request('shop'), true);

        $manager_settings = DB::get('settings', 'OutOfStockManager.json');

        $body_tmpl = new Template();
        $body_tmpl->parse(nl2br($manager_settings['body']));

        $body_custom = $body_tmpl->render([
          'shop' => $data_shop['shop'],
          'product' => $data_product['product']
        ]);

        $subject_tmpl = new Template();
        $subject_tmpl->parse($manager_settings['subject']);

        $subject = $subject_tmpl->render([
          'shop' => $data_shop['shop'],
          'product' => $data_product['product']
        ]);

        $body = $tmpl->render([
          'shop' => $data_shop['shop'],
          'product' => $data_product['product'],
          'body' => $body_custom
        ]);

        $info = self::info();

        if ($info['queue']) {

          DB::queue([
            'shop' => $_SESSION['shop'],
            'app' => 'OutOfStockManager',
            'type' => 'email',
            'data' => [
              'email' => $item['emails'],
              'subject' => $subject,
              'body' => $body
            ]
          ]);

        } else {

          Helpers::email([
            'email' => $item['emails'],
            'subject' => $subject,
            'body' => $body
          ]);

        }

        unset($items['inventory_id_' . $data['inventory_item_id']]);
        DB::put('products', $items, 'OutOfStockManager.json');

      }

    }

    Helpers::success();
    
  }

  public function add()
  {

    if (isset($_REQUEST['product_id']) && isset($_REQUEST['variant_id']) && isset($_REQUEST['email'])) {

      if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
        Helpers::error(400);
      }

      $products = DB::get('products', 'OutOfStockManager.json');
      $product_json = json_decode(Shopify::request('products/' . $_REQUEST['product_id']), true);
      $product = $product_json['product'];

      if ($_REQUEST['variant_id'] == null) {

        $variant = reset($product['variants']);

      } else {

        foreach ($product['variants'] as $var) {
          
          if ($var['id'] == $_REQUEST['variant_id']) {

            $variant = $var;

          }

        }

      }

      if (isset($products['inventory_id_' . $variant['inventory_item_id']])) {

        $emails = $products['inventory_id_' . $variant['inventory_item_id']]['emails'];

        if (!in_array($_REQUEST['email'], $emails)) {

          $emails[] = $_REQUEST['email'];
          $products['inventory_id_' . $variant['inventory_item_id']]['emails'] = $emails;

        }

      } else {

        if ($_REQUEST['variant_id'] == null) {
          $variant_title = '';
        } else {
          if ($variant['title'] == 'Default Title') {
            $variant_title = '';
          } else {
            $variant_title = $variant['title'];
          }
        }

        $res = json_decode(Shopify::request('products/' . $_REQUEST['product_id']), true);
        $image = '';

        if ($_REQUEST['variant_id']) {
          $found = false;

          foreach ($res['product']['images'] as $img) {

            if (in_array($_REQUEST['variant_id'], $img['variant_ids']) && !$found) {

              $image = $img['src'];
              $found = true;

            }

          }

          if (!$found && isset($res['product']['images'][0])) {

            $image = $res['product']['images'][0]['src'];

          }

        } else {

          if (isset($res['product']['images'][0])) {

            $image = $res['product']['images'][0]['src'];

          }

        }

        $products['inventory_id_' . $variant['inventory_item_id']] = [
          'image' => $image,
          'product' => [
            'id' => $_REQUEST['product_id'],
            'title' => $product['title']
          ],
          'variant' => [
            'id' => $_REQUEST['variant_id'],
            'title' => $variant_title
          ],
          'emails' => [$_REQUEST['email']]
        ];

      }

      DB::put('products', $products, 'OutOfStockManager.json');

    }

    Helpers::success();

  }
  
}