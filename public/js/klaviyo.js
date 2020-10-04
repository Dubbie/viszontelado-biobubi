var KlaviyoIntegration = function() {
    var debug = true;
    // ShopRenter.onCartUpdate(function(event) {
    //     console.log(event.detail)
    // });

    // Segédfunkció
    function log(msg) {
        if (debug) {
            console.log('klaviyoIntegráció: ' + msg);
        }
    }

    function bindActiveOnSite() {

    }

    function bindViewedProduct() {
        if (ShopRenter.product) {
            var _learnq = _learnq || [];

            log('Termék megtekintve (SKU: ' + ShopRenter.product.sku + ')');
            $.ajax({
                type: 'POST',
                url: 'https://viszontelado.semmiszemet.hu/sr/termek-lekerdezes',
                data: {
                    sku: ShopRenter.product.sku
                },
                success: function(res) {
                    console.log(res);
                    var item = ShopRenter.product;

                    _learnq.push(["track", "Viewed Product", item]);

                    _learnq.push(["trackViewedItem", {
                        "id": item.id,
                        "sku": item.sku,
                        "parent": {
                            "id": item.parent.id,
                            "sku": item.parent.sku
                        },
                    }]);
                },
                error: function(request, status, error) {
                    console.error(request.responseText);
                }
            });
        }
    }

    function bindAddedToCart() {
        ShopRenter.onItemAdd(function(event) {
            var cartData = {
                "AddedItemProductName": event.detail.product.name,
                "AddedItemProductID": JSON.parse(event.detail.product.id)[0],
                "AddedItemSKU": event.detail.product.sku,
                "AddedItemPrice": parseFloat(event.detail.product.price),
                "AddedItemQuantity": event.detail.product.quantity,
                "Items": [],
                "ItemNames": [],
                "CheckoutURL": 'https://biobubi.hu/checkout',
            };

            $.ajax({
                type: 'GET',
                url: '/cart.json',
                success: function(res) {
                    for (const item of res.items) {
                        cartData['Items'].push({
                            "ProductID": item.id,
                            "SKU": item.sku,
                            "ProductName": item.name,
                            "Quantity": item.quantity,
                            "ItemPrice": item.price,
                            "RowTotal": item.total,
                            "ProductURL": item.href,
                        });

                        cartData['ItemNames'].push(item.name);
                    }
                    cartData['$value'] = res.total;
                    console.log('Data from ShopRenter: ');
                    console.log(res);
                    console.log('Cart data to be sent to Klaviyo:');
                    console.log(cartData);

                    _learnq.push(["track", "Added to Cart", cartData]);
                },
                error: function(request, status, error) {
                    console.error(request.responseText);
                }
            });
        });
    }

    function bindStartedCheckout() {
        $(document).on('click', '.navigation-button', e => {
            var email = document.getElementById('email');
            var firstname = document.getElementById('firstname');
            var lastname = document.getElementById('lastname');

            if (email && email.value.length > 0) {
                var _learnq = _learnq || [];
                _learnq.push(['identify', {
                    '$email': email.value,
                    '$first_name': firstname.value,
                    '$last_name': lastname.value
                }]);

                var cartData = {
                    "$event_id": "",
                    "Items": [],
                    "ItemNames": [],
                    "CheckoutURL": 'https://biobubi.hu/checkout',
                };

                $.ajax({
                    type: 'GET',
                    url: '/cart.json',
                    success: function(res) {
                        for (const item of res.items) {
                            cartData['Items'].push({
                                "ProductID": item.id,
                                "SKU": item.sku,
                                "ProductName": item.name,
                                "Quantity": item.quantity,
                                "ItemPrice": item.price,
                                "RowTotal": item.total,
                                "ProductURL": item.href,
                            });

                            cartData['ItemNames'].push(item.name);
                        }
                        cartData['$value'] = res.total;
                        cartData['$event_id'] = res.token + '_' + Math.round(+new Date()/1000);
                        console.log('Data from ShopRenter: ');
                        console.log(res);
                        console.log('Cart data to be sent to Klaviyo Checkout:');
                        console.log(cartData);

                        _learnq.push(["track", "Started Checkout", cartData]);
                    },
                    error: function(request, status, error) {
                        console.error(request.responseText);
                    }
                });
            }
        });
    }

    function init() {
        log('Inicializálás...');
        console.log(ShopRenter);

        var _learnq = _learnq || [];

        _learnq.push(['identify', {
            // Change the line below to dynamically print the user's email.
            '$email' : ShopRenter.customer.email
        }]);

        bindActiveOnSite();
        bindViewedProduct();
        bindAddedToCart();
        bindStartedCheckout();
    }

    return {
        init: init
    }
};

$(function() {
    var klaviyo = new KlaviyoIntegration();
    klaviyo.init();
});