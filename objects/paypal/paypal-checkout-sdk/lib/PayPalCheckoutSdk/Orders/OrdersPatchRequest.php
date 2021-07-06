<?php

// This class was generated on Wed, 01 Aug 2018 16:35:39 PDT by version 0.1.0-dev+0ee05a-dirty of Braintree SDK Generator
// OrdersPatchRequest.php
// @version 0.1.0-dev+0ee05a-dirty
// @type request
// @data H4sIAAAAAAAC/+RXTW8jNwy991cIOg/sYFv0MDcjSZF+7MZN3AWKbWAzEp3R7sxQpaikRuD/Xmg0/ho33W67TVHkZOuJ1LwnUiL1qN9Ag7rUxBY5jDyIqXShzzAYdl4ctbrUP3kLgkFBqzq7kfqZojLQqtjNbCfUg5NKLU6vziez87OFIlaLyXR6dfk2jYKAxLB1bkme8r98Pf3hfLbvM6tQLamu6cG1dwpE2N3GzMkqun2PRoICRtUpgNsay1/iycmXJtbdL+ZR7fZHhmz+h64VbCWj4x08UtfRe2JBq8gjQ9oQ5YIa+jP6GgweL9Ajh999ioWPbCoIOI+tk/DX2GTVw5XA2iP3bqueg/i7m5GJQaiZO/ufinjCuKH7zy7Y7g7MS5HsYYU4wgZc/X9O1VA57117NwdrGUN4KeELtJT5Jm2JX4psaCg+300/3pQfXegfI/JqCgwNCnLQ5bubQl8gWOQh+g1xM8SmINUB9qhnK59qdxB27Z0u9Ftgl0rffk2fO6sL/T2uevCouKfK+u2ZoqWSCvsyLNRX5pEu9IQZVvlTJ4W+QrCXbb3S5RLqgAn4NTpGq0vhiIWecto/cRh02ca6Xt9kGwySF9nynvbNxoB2V8HnnF0OuA9nDoVMWgWJapLy3fXlm9wKbJsDIQXe1yvlgcVB3SvsJhgDRTYYBnq//lO9PbAv+ONBWTI1B6J64DgoWQOl1qSLSAqPAN+hKEsmNtiKqsnkTE2rqIfKmSqZpoPT2d9DHXGkNpzVkrjDF8liscv0T4rzH+juAl18PCP9gfRueCx8d/6ElKHG1/iJmfj3GXqQaphz1T8PD8guOBtF+bz9SxHIWX+sr0uIA4Eb5FhhN7M9N7kHX+RbeS91lCUMKjXznPkp6NPucyi6WSer4KkNmNdJcKFPqevYe7WJoMtbPX4fqNWFvhDxr1EqsummmcxOL3S+Q3Wpx/evxqZC84GijPPLZ/y4uS3XutDXH5zfcjr/zaMRtNfdU+SULOry1clX6y9+BwAA//8=
// DO NOT EDIT

namespace PayPalCheckoutSdk\Orders;

use PayPalHttp\HttpRequest;

class OrdersPatchRequest extends HttpRequest
{
    function __construct($orderId)
    {
        parent::__construct("/v2/checkout/orders/{order_id}?", "PATCH");

        $this->path = str_replace("{order_id}", urlencode($orderId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }



}
