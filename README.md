<p align="center"><a href="https://www.sellfino.com" target="_blank" rel="noopener noreferrer"><img width="200" src="https://www.sellfino.com/images/logo.png" alt="Sellfino logo"></a></p>

---

## Out of Stock Manager
Let your customers know when out of stock products are available again.

## Demo & Screenshots
Check how this app works in the live store: [DEMO](https://sellfino.myshopify.com/products/aviator-glasses-outofstock)

<a href="https://sellfino.com/images/screens/out/out-1.jpg" target="_blank" rel="noopener noreferrer"><img width="19%" src="https://sellfino.com/images/screens/out/out-1.jpg"></a> <a href="https://sellfino.com/images/screens/out/out-2.jpg" target="_blank" rel="noopener noreferrer"><img width="19%" src="https://sellfino.com/images/screens/out/out-2.jpg"></a> <a href="https://sellfino.com/images/screens/out/out-3.jpg" target="_blank" rel="noopener noreferrer"><img width="19%" src="https://sellfino.com/images/screens/out/out-3.jpg"></a>

## Installation
:exclamation: Make sure that you provided SMTP settings for the Sellfino platform correctly. Without that, no email will be sent.

- **1.** Add this app to Sellfino App Store.
- **2.** Open *Settings* and provide subject and body for the email that will be sent to customers, when out of stock product is in stock again.
- **3.** You can overwrite `notification.html` by adding this file under `/stores/your-app-domain.myshopify.com/OutOfStockManager/emails/notification.html`.
- **4.** Modify your frontend code. Here is the example of the AJAX call that will add customer's email to the waiting list for the unavailable product (we use jQuery here, because it's the most popular JS library - please change it accordingly):
```javascript
<script>
  $.ajax({
    url: 'https://your-app-domain.com/app/OutOfStockManager/add?shop={{ shop.permanent_domain }}',
    method: 'post',
    data: {
      product_id: {{ product.id }},
      variant_id: {% if product.first_available_variant.id %}{{ product.first_available_variant.id  }}{% else %}''{% endif %},
      email: 'test@test.com'
    }
  })
</script>
```
- **5.** If your product has different variants, you need to modify the code, so `variant_id` will be correctly set. Email should be also changed to what the customer provides.

## Sellfino Open Source Shopify App Store
This is the app for [Sellfino](https://github.com/sellfino/sellfino) platform.

#### Support and Contribution

Join our awesome community! Here is how you can connect with us:
- [Website](https://www.sellfino.com) - all info here + live chat
- [Discord](https://discordapp.com/invite/wrFnzZ3) - channels to discuss new ideas and ask for help
- [Messanger](https://m.me/104484064333760) - if you want to chat on Facebook
- [Email](mailto:contact@sellfino.com) - whenever we are out of touch, drop us a message


## Copyright
Copyright (c) 2019-present, Lucas Szarzynski
