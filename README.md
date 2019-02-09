# fractal

Convenience wrapper for [Fractal](https://fractal.thephpleague.com/).

- [Requirements](#requirements)
- [Install](#install)
- [Usage](#usage)
  - [Item examples](#item-examples)
    - [Callback for transformer](#callback-for-transformer)
    - [Class for transformer](#class-for-transformer)
  - [Collection example](#collection-example)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Requirements

This package requires PHP 7.2 or higher.

## Install

Via composer

``` bash
$ composer require rkulik/fractal
```

## Usage

As this package wraps Fractal, the general usage is pretty much the same. The examples listed below demonstrate the
basic workflow. For further information please refer to the [Fractal documentation](https://fractal.thephpleague.com/).

- [Item examples](#item-examples)
- [Collection example](#collection-example)

### Item examples

In the following examples an item gets transformed and returned as an array. The transformation is done using either a
[callback](#callback-for-transformer) or a [class](#class-for-transformer).

#### Callback for transformer

``` php
<?php

require 'vendor/autoload.php';

$fractal = new \Rkulik\Fractal\Fractal(new \League\Fractal\Manager());

$product = [
    'id' => '123',
    'name' => 'T-shirt',
    'price' => '1290',
    'brand_name' => 'Nike',
    'gender' => 'm',
];

$transformer = function (array $product) {
    return [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'price' => (int)$product['price'],
        'brand' => $product['brand_name'],
        'gender' => $product['gender'] === 'm' ? 'male' : 'female',
    ];
};

$item = $fractal->item($product, $transformer)->toArray();
```

#### Class for transformer

Using classes for transformation is the recommended way to do so, as those transformers are easily reusable.

``` php
<?php

require 'vendor/autoload.php';

class Transformer extends \League\Fractal\TransformerAbstract {
    public function transform(array $product): array
    {
        return [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'price' => (int)$product['price'],
            'brand' => $product['brand_name'],
            'gender' => $product['gender'] === 'm' ? 'male' : 'female',
        ];
    }
}

$fractal = new \Rkulik\Fractal\Fractal(new \League\Fractal\Manager());

$product = [
    'id' => '123',
    'name' => 'T-shirt',
    'price' => '1290',
    'brand_name' => 'Nike',
    'gender' => 'm',
];

$item = $fractal->item($product, new Transformer())->toArray();
```

### Collection example

Transforming and paginating a collection using a cursor can be achieved as follows:

``` php
<?php

require 'vendor/autoload.php';

$fractal = new \Rkulik\Fractal\Fractal(new \League\Fractal\Manager());

$products = [
    [
        'id' => '123',
        'name' => 'T-shirt',
        'price' => '1290',
        'brand_name' => 'Nike',
        'gender' => 'm',
    ],
    [
        'id' => '456',
        'name' => 'Jacket',
        'price' => '19900',
        'brand_name' => 'Carhartt',
        'gender' => 'f',
    ],
    [
        'id' => '789',
        'name' => 'Trousers',
        'price' => '3990',
        'brand_name' => 'Only & Sons',
        'gender' => 'f',
    ],
];

$transformer = function (array $product) {
    return [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'price' => (int)$product['price'],
        'brand' => $product['brand_name'],
        'gender' => $product['gender'] === 'm' ? 'male' : 'female',
    ];
};

$cursor = new \League\Fractal\Pagination\Cursor(null, null, 2, 3);

$collection = $fractal->collection([
    $products[0],
    $products[1],
], $transformer)->setCursor($cursor)->toArray();
```

## Testing

``` bash
$ composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email rene@kulik.io instead of using the issue tracker.

## Credits

- [René Kulik](https://github.com/rkulik)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
