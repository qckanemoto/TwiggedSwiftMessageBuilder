# TwiggedSwiftMessageBuilder

[![Build Status](https://travis-ci.org/qckanemoto/TwiggedSwiftMessageBuilder.svg?branch=master)](https://travis-ci.org/qckanemoto/TwiggedSwiftMessageBuilder)
[![Latest Stable Version](https://poser.pugx.org/qckanemoto/twigged-swiftmessage-builder/v/stable.svg)](https://packagist.org/packages/qckanemoto/twigged-swiftmessage-builder)
[![Total Downloads](https://poser.pugx.org/qckanemoto/twigged-swiftmessage-builder/downloads.svg)](https://packagist.org/packages/qckanemoto/twigged-swiftmessage-builder)

`TwiggedSwiftMessageBuilder` class allows you following things:

 * to create Twig templated Swift_Message
 * to create inline styled html email from unstyled html and css strings
 * to embed some image files into message body

## Requirements

* PHP 5.3+

## Getting started

First add this dependency into your `composer.json`:

```json
{
    "require": {
        "qckanemoto/twigged-swiftmessage-builder": "dev-master"
    }
}
```

Then you can send Twig templated email as below:

```twig
{# email.txt.twig #}

{% block from %}no-reply@example.com{% endblock %}
{% block from_name %}[Example]{% endblock %}
{% block subject %}Welcome to [Example]!{% endblock %}

{% block body %}
Hello [Example] World!
{% endblock %}
```

```php
// in your application.

$builder = new \Qck\TwiggedSwiftMessageBuilder($twig);  // $twig is an instance of \Twig_Environment class.

$message = $builder->buildMessage('email.txt.twig');
$message->setTo('hoge@example.com');

$mailer->send($message);    // $mailer is an instance of \Swift_Mailer class.
```

In Twig template you can define many things by using `{% block [field-name] %}{% endblock %}`.
These fields can be defined.

 * from
 * from_name
 * to
 * cc
 * bcc
 * reply_to
 * subject
 * body

## Use variables in Twig template

Offcourse you can pass variables and use them in Twig template with `{{ vars }}` as below:

```twig
{# email.txt.twig #}

{% block subject %}Welcome to {{ site_title }}!{% endblock %}
```

```php
// in your application.

$builder = new \Qck\TwiggedSwiftMessageBuilder($twig);

$message = $builder->buildMessage('email.txt.twig', array(
    'site_title' => 'FooBar Service',
));
$message->setTo('hoge@example.com');

$mailer->send($message);
```

## Use inline-styled html email

You can make inline-styled html from unstyled html and css strings.
To allow recipients of your html email to receive it with Gmail, you will have to make inline-styled html body.

```php
// in your application.

$builder = new \Qck\TwiggedSwiftMessageBuilder($twig);

$message = $builder->buildMessage('email.html.twig');

$style = file_get_contents('/path/to/style.css');

$message = $builder->setInlineStyle($message, $style);

$mailer->send($message);
```

> **Note**
>
> This functionality is using `mb_convert_encoding()` with `'auto'` internally. So if you use this you **must** set `mbstring.language` in php.ini or call `mb_language('your_language')` on ahead.
>
> **注意**
>
> この機能は内部的に `mb_convert_encoding()` に `'auto'` を渡して実行します。なので、php.ini で `mbstring.language` を設定するか、`mb_language('Japanese')` を事前に実行しておく必要があります。

## Embed some image files into message body

You can embed images into message body as below:

```twig
{# email.html.twig #}

{% block body %}
<img src="{{ embed_image(image_path) }}"/>
{% endblock %}
```

```php
// in your application.

$builder = new \Qck\TwiggedSwiftMessageBuilder($twig);

$message = $builder->buildMessage('email.html.twig', array(
    'image_path' => '/path/to/image/file',
));

// you can get renderable html with base64 encoded images. (In case you want to print preview.)
$renderableHtml = $builder->renderBody($message);

// you must finish embedding before send message.
$message = $builder->finishEmbedImage($message);

$mailer->send($message);
```

## Enjoy!

See also [functional tests](tests/FunctionalTest.php) to understand basic usages.
