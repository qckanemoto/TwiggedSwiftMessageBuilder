<?php
namespace Qck\TwiggedSwiftMessageBuilder;

use Qck\TwiggedSwiftMessageBuilder\ImageEmbedder\Embedder;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    private $builder;

    protected function setUp()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__ . '/templates'));
        $this->builder = new TwiggedSwiftMessageBuilder($twig, new Embedder(), new CssToInlineStyles());
    }

    /**
     * @test
     * @large
     */
    public function create_simple_twig_message()
    {
        /** @var \Swift_Message $message */
        $message = $this->builder->buildMessage('simple.txt.twig');

        $this->assertEquals($message->getFrom(), array('from@example.com' => 'from_name'));
        $this->assertEquals($message->getSubject(), 'subject');
        $this->assertEquals($message->getTo(), array('to@example.com' => null));
        $this->assertEquals($message->getCc(), array('cc@example.com' => null));
        $this->assertEquals($message->getBcc(), array('bcc@example.com' => null));
        $this->assertEquals($message->getReplyTo(), array('reply_to@example.com' => null));
        $this->assertEquals($message->getBody(), 'body');
        $this->assertEquals($message->getContentType(), 'text/plain');
    }

    /**
     * @test
     * @large
     */
    public function create_twig_message_with_extends()
    {
        /** @var \Swift_Message $message */
        $message = $this->builder->buildMessage('extended.txt.twig');

        $this->assertEquals($message->getSubject(), 'extended_subject');
    }

    /**
     * @test
     * @large
     */
    public function create_twig_message_with_vars()
    {
        $vars = array(
            'to' => 'takashi@example.com',
            'name' => 'Takashi',
        );

        /** @var \Swift_Message $message */
        $message = $this->builder->buildMessage('vars.txt.twig', $vars);

        $this->assertEquals($message->getTo(), array('takashi@example.com' => null));
        $this->assertEquals($message->getBody(), 'Hi, Takashi.');
    }

    /**
     * @test
     * @large
     */
    public function create_html_twig_message()
    {
        /** @var \Swift_Message $message */
        $message = $this->builder->buildMessage('simple.html.twig');

        $this->assertEquals($message->getBody(), '<p>test</p>');
        $this->assertEquals($message->getContentType(), 'text/html');
    }

    /**
     * @test
     * @large
     *
     * You can make inline-styled html from unstyled html and css strings.
     * To allow recipients of your html email to receive it with Gmail, you will have to make inline-styled html body.
     */
    public function create_html_twig_message_with_style_tag()
    {
        /** @var \Swift_Message $message */
        $message = $this->builder->buildMessage('simple.html.twig');

        $this->assertEquals($message->getBody(), '<p>test</p>');

        $style = 'p { color: #fff; }';

        $message = $this->builder->setInlineStyle($message, $style);

        $this->assertContains('<p style="color: #fff;">test</p>', $message->getBody());
    }

    /**
     * @test
     * @large
     *
     * You can embed images into message body by following steps.
     *
     * 1. Put `{{ embed_image('/full/path/to/image/file') }}` in your Twig template
     * 2. After do `buildMessage`, execute `$message = finalizeEmbedding($message);`
     *
     * Then images are correctly embedded into `$message`.
     */
    public function create_html_twig_message_with_embedded_images()
    {
        $imagePath = __DIR__ . '/templates/images/silex.png';

        /** @var \Swift_Message $message */
        $message = $this->builder->buildMessage('embedding.html.twig', array(
            'image_path' => $imagePath,
        ));

        $this->assertContains($imagePath, $message->getBody());

        $message = $this->builder->finalizeEmbedding($message);

        $this->assertContains('<img src="cid:', $message->getBody());
    }

    /**
     * @test
     * @large
     *
     * You can get html with embedded images which are base64-encoded so that it can be previewed on browser.
     */
    public function create_html_twig_message_with_embedded_images_and_preview()
    {
        $imagePath = __DIR__ . '/templates/images/silex.png';

        /** @var \Swift_Message $message */
        $message = $this->builder->buildMessage('embedding.html.twig', array(
            'image_path' => $imagePath,
        ));

        $html = $this->builder->renderBody($message);

        $this->assertContains('<img src="data:image/png;base64,', $html);
    }
}
