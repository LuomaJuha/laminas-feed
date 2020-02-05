<?php

/**
 * @see       https://github.com/laminas/laminas-feed for the canonical source repository
 * @copyright https://github.com/laminas/laminas-feed/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-feed/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Feed\Writer;

use DateTime;
use DateTimeImmutable;
use Laminas\Feed\Writer;
use Laminas\Feed\Writer\Exception\ExceptionInterface;
use Laminas\Feed\Writer\Extension\ITunes\Entry;
use Laminas\Feed\Writer\Source;
use PHPUnit\Framework\TestCase;

/**
 * @group      Laminas_Feed
 * @group      Laminas_Feed_Writer
 */
class EntryTest extends TestCase
{
    protected $feedSamplePath = null;

    public function setup()
    {
        $this->feedSamplePath = dirname(__FILE__) . '/_files';
        Writer\Writer::reset();
    }

    public function tearDown()
    {
        Writer\Writer::reset();
    }

    public function testAddsAuthorNameFromArray()
    {
        $entry = new Writer\Entry;
        $entry->addAuthor(['name' => 'Joe']);
        $this->assertEquals([['name' => 'Joe']], $entry->getAuthors());
    }

    public function testAddsAuthorEmailFromArray()
    {
        $entry = new Writer\Entry;
        $entry->addAuthor(['name' => 'Joe',
                                'email' => 'joe@example.com']);
        $this->assertEquals([['name'  => 'Joe',
                                        'email' => 'joe@example.com']], $entry->getAuthors());
    }

    public function testAddsAuthorUriFromArray()
    {
        $entry = new Writer\Entry;
        $entry->addAuthor(['name' => 'Joe',
                                'uri' => 'http://www.example.com']);
        $this->assertEquals([['name' => 'Joe',
                                        'uri' => 'http://www.example.com']], $entry->getAuthors());
    }

    public function testAddAuthorThrowsExceptionOnInvalidNameFromArray()
    {
        $this->expectException(
            Writer\Exception\InvalidArgumentException::class
        );

        $entry = new Writer\Entry;
        $entry->addAuthor(['name' => '']);
    }

    public function testAddAuthorThrowsExceptionOnInvalidEmailFromArray()
    {
        $this->expectException(
            Writer\Exception\InvalidArgumentException::class
        );

        $entry = new Writer\Entry;
        $entry->addAuthor(['name' => 'Joe', 'email' => '']);
    }

    public function testAddAuthorThrowsExceptionOnInvalidUriFromArray()
    {
        $this->expectException(
            Writer\Exception\InvalidArgumentException::class
        );

        $entry = new Writer\Entry;
        $entry->addAuthor(
            [
                'name'  => 'Joe',
                'email' => 'joe@example.org',
                'uri'   => '',
            ]
        );
    }

    public function testAddAuthorThrowsExceptionIfNameOmittedFromArray()
    {
        $this->expectException(
            Writer\Exception\InvalidArgumentException::class
        );

        $entry = new Writer\Entry;
        $entry->addAuthor(['uri' => 'notauri']);
    }

    public function testAddsAuthorsFromArrayOfAuthors()
    {
        $entry = new Writer\Entry;
        $entry->addAuthors([
                                ['name' => 'Joe',
                                      'uri' => 'http://www.example.com'],
                                ['name' => 'Jane',
                                      'uri' => 'http://www.example.com']
                           ]);
        $expected = [
            ['name' => 'Joe',
                  'uri' => 'http://www.example.com'],
            ['name' => 'Jane',
                  'uri' => 'http://www.example.com']
        ];
        $this->assertEquals($expected, $entry->getAuthors());
    }

    public function testAddsEnclosure()
    {
        $entry = new Writer\Entry;
        $entry->setEnclosure([
                                  'type'   => 'audio/mpeg',
                                  'uri'    => 'http://example.com/audio.mp3',
                                  'length' => '1337'
                             ]);
        $expected = [
            'type'   => 'audio/mpeg',
            'uri'    => 'http://example.com/audio.mp3',
            'length' => '1337'
        ];
        $this->assertEquals($expected, $entry->getEnclosure());
    }

    public function testAddsEnclosureThrowsExceptionOnMissingUri()
    {
        $this->expectException(ExceptionInterface::class);
        $this->markTestIncomplete('Pending Laminas\URI fix for validation');
        $entry = new Writer\Entry;
        $entry->setEnclosure([
                                  'type'   => 'audio/mpeg',
                                  'length' => '1337'
                             ]);
    }

    public function testAddsEnclosureThrowsExceptionWhenUriIsInvalid()
    {
        $this->expectException(ExceptionInterface::class);
        $this->markTestIncomplete('Pending Laminas\URI fix for validation');
        $entry = new Writer\Entry;
        $entry->setEnclosure([
                                  'type'   => 'audio/mpeg',
                                  'uri'    => 'http://',
                                  'length' => '1337'
                             ]);
    }

    public function testSetsCopyright()
    {
        $entry = new Writer\Entry;
        $entry->setCopyright('Copyright (c) 2009 Paddy Brady');
        $this->assertEquals('Copyright (c) 2009 Paddy Brady', $entry->getCopyright());
    }

    public function testSetCopyrightThrowsExceptionOnInvalidParam()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setCopyright('');
    }

    public function testSetsContent()
    {
        $entry = new Writer\Entry;
        $entry->setContent('I\'m content.');
        $this->assertEquals("I'm content.", $entry->getContent());
    }

    public function testSetContentThrowsExceptionOnInvalidParam()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setContent('');
    }

    public function testSetDateCreatedDefaultsToCurrentTime()
    {
        $entry = new Writer\Entry;
        $entry->setDateCreated();
        $dateNow = new DateTime();
        $this->assertLessThanOrEqual($dateNow, $entry->getDateCreated());
    }

    public function testSetDateCreatedUsesGivenUnixTimestamp()
    {
        $entry = new Writer\Entry;
        $entry->setDateCreated(1234567890);
        $myDate = new DateTime('@' . 1234567890);
        $this->assertEquals($myDate, $entry->getDateCreated());
    }

    /**
     * @group Laminas-12070
     */
    public function testSetDateCreatedUsesGivenUnixTimestampWhenItIsLessThanTenDigits()
    {
        $entry = new Writer\Entry;
        $entry->setDateCreated(123456789);
        $myDate = new DateTime('@' . 123456789);
        $this->assertEquals($myDate, $entry->getDateCreated());
    }

    /**
     * @group Laminas-11610
     */
    public function testSetDateCreatedUsesGivenUnixTimestampWhenItIsAVerySmallInteger()
    {
        $entry = new Writer\Entry;
        $entry->setDateCreated(123);
        $myDate = new DateTime('@' . 123);
        $this->assertEquals($myDate, $entry->getDateCreated());
    }

    public function testSetDateCreatedUsesDateTimeObject()
    {
        $myDate = new DateTime('@' . 1234567890);
        $entry = new Writer\Entry;
        $entry->setDateCreated($myDate);
        $this->assertEquals($myDate, $entry->getDateCreated());
    }

    public function testSetDateCreatedUsesDateTimeImmutableObject()
    {
        $myDate = new DateTimeImmutable('@' . 1234567890);
        $entry = new Writer\Entry;
        $entry->setDateCreated($myDate);
        $this->assertEquals($myDate, $entry->getDateCreated());
    }

    public function testSetDateModifiedDefaultsToCurrentTime()
    {
        $entry = new Writer\Entry;
        $entry->setDateModified();
        $dateNow = new DateTime();
        $this->assertLessThanOrEqual($dateNow, $entry->getDateModified());
    }

    public function testSetDateModifiedUsesGivenUnixTimestamp()
    {
        $entry = new Writer\Entry;
        $entry->setDateModified(1234567890);
        $myDate = new DateTime('@' . 1234567890);
        $this->assertEquals($myDate, $entry->getDateModified());
    }

    /**
     * @group Laminas-12070
     */
    public function testSetDateModifiedUsesGivenUnixTimestampWhenItIsLessThanTenDigits()
    {
        $entry = new Writer\Entry;
        $entry->setDateModified(123456789);
        $myDate = new DateTime('@' . 123456789);
        $this->assertEquals($myDate, $entry->getDateModified());
    }

    /**
     * @group Laminas-11610
     */
    public function testSetDateModifiedUsesGivenUnixTimestampWhenItIsAVerySmallInteger()
    {
        $entry = new Writer\Entry;
        $entry->setDateModified(123);
        $myDate = new DateTime('@' . 123);
        $this->assertEquals($myDate, $entry->getDateModified());
    }

    public function testSetDateModifiedUsesDateTimeObject()
    {
        $myDate = new DateTime('@' . 1234567890);
        $entry = new Writer\Entry;
        $entry->setDateModified($myDate);
        $this->assertEquals($myDate, $entry->getDateModified());
    }

    public function testSetDateModifiedUsesDateTimeImmutableObject()
    {
        $myDate = new DateTimeImmutable('@' . 1234567890);
        $entry = new Writer\Entry;
        $entry->setDateModified($myDate);
        $this->assertEquals($myDate, $entry->getDateModified());
    }

    public function testSetDateCreatedThrowsExceptionOnInvalidParameter()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setDateCreated('abc');
    }

    public function testSetDateModifiedThrowsExceptionOnInvalidParameter()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setDateModified('abc');
    }

    public function testGetDateCreatedReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getDateCreated());
    }

    public function testGetDateModifiedReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getDateModified());
    }

    public function testGetCopyrightReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getCopyright());
    }

    public function testGetContentReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getContent());
    }

    public function testSetsDescription()
    {
        $entry = new Writer\Entry;
        $entry->setDescription('abc');
        $this->assertEquals('abc', $entry->getDescription());
    }

    public function testSetDescriptionThrowsExceptionOnInvalidParameter()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setDescription('');
    }

    public function testGetDescriptionReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getDescription());
    }

    public function testSetsId()
    {
        $entry = new Writer\Entry;
        $entry->setId('http://www.example.com/id');
        $this->assertEquals('http://www.example.com/id', $entry->getId());
    }

    public function testSetIdThrowsExceptionOnInvalidParameter()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setId('');
    }

    public function testGetIdReturnsNullIfNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getId());
    }

    public function testSetsLink()
    {
        $entry = new Writer\Entry;
        $entry->setLink('http://www.example.com/id');
        $this->assertEquals('http://www.example.com/id', $entry->getLink());
    }

    public function testSetLinkThrowsExceptionOnEmptyString()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setLink('');
    }

    public function testSetLinkThrowsExceptionOnInvalidUri()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setLink('http://');
    }

    public function testGetLinkReturnsNullIfNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getLink());
    }

    public function testGetLinksReturnsNullIfNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getLinks());
    }

    public function testSetsCommentLink()
    {
        $entry = new Writer\Entry;
        $entry->setCommentLink('http://www.example.com/id/comments');
        $this->assertEquals('http://www.example.com/id/comments', $entry->getCommentLink());
    }

    public function testSetCommentLinkThrowsExceptionOnEmptyString()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setCommentLink('');
    }

    public function testSetCommentLinkThrowsExceptionOnInvalidUri()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setCommentLink('http://');
    }

    public function testGetCommentLinkReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getCommentLink());
    }

    public function testSetsCommentFeedLink()
    {
        $entry = new Writer\Entry;

        $entry->setCommentFeedLink(['uri' => 'http://www.example.com/id/comments',
                                         'type' => 'rdf']);
        $this->assertEquals([['uri' => 'http://www.example.com/id/comments',
                                        'type' => 'rdf']], $entry->getCommentFeedLinks());
    }

    public function testSetCommentFeedLinkThrowsExceptionOnEmptyString()
    {
        $this->markTestIncomplete('Pending Laminas\URI fix for validation');

        $this->expectException(ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setCommentFeedLink(
            [
                'uri'  => '',
                'type' => 'rdf',
            ]
        );
    }

    public function testSetCommentFeedLinkThrowsExceptionOnInvalidUri()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setCommentFeedLink(
            [
                'uri'  => 'http://',
                'type' => 'rdf',
            ]
        );
    }

    public function testSetCommentFeedLinkThrowsExceptionOnInvalidType()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setCommentFeedLink(
            [
                'uri'  => 'http://www.example.com/id/comments',
                'type' => 'foo',
            ]
        );
    }

    public function testGetCommentFeedLinkReturnsNullIfNoneSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getCommentFeedLinks());
    }

    public function testSetsTitle()
    {
        $entry = new Writer\Entry;
        $entry->setTitle('abc');
        $this->assertEquals('abc', $entry->getTitle());
    }

    public function testSetTitleThrowsExceptionOnInvalidParameter()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setTitle('');
    }

    public function testGetTitleReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getTitle());
    }

    public function testSetsCommentCount()
    {
        $entry = new Writer\Entry;
        $entry->setCommentCount('10');
        $this->assertEquals(10, $entry->getCommentCount());
    }

    public function testSetsCommentCount0()
    {
        $entry = new Writer\Entry;
        $entry->setCommentCount(0);
        $this->assertEquals(0, $entry->getCommentCount());
    }

    public function allowedCommentCounts()
    {
        return [
            [0, 0],
            [0.0, 0],
            [1, 1],
            [PHP_INT_MAX, PHP_INT_MAX],
        ];
    }

    /**
     * @dataProvider allowedCommentCounts
     */
    public function testSetsCommentCountAllowed($count, $expected)
    {
        $entry = new Writer\Entry;
        $entry->setCommentCount($count);
        $this->assertSame($expected, $entry->getCommentCount());
    }

    public function disallowedCommentCounts()
    {
        return [
            [1.1],
            [-1],
            [-PHP_INT_MAX],
            [[]],
            [''],
            [false],
            [true],
            [new \stdClass],
            [null],
        ];
    }

    /**
     * @dataProvider disallowedCommentCounts
     */
    public function testSetsCommentCountDisallowed($count)
    {
        $entry = new Writer\Entry;
        $this->expectException(ExceptionInterface::class);
        $entry->setCommentCount($count);
    }

    public function testSetCommentCountThrowsExceptionOnInvalidEmptyParameter()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setCommentCount('');
    }

    public function testSetCommentCountThrowsExceptionOnInvalidNonIntegerParameter()
    {
        $this->expectException(Writer\Exception\ExceptionInterface::class);

        $entry = new Writer\Entry;
        $entry->setCommentCount('a');
    }

    public function testGetCommentCountReturnsNullIfDateNotSet()
    {
        $entry = new Writer\Entry;
        $this->assertNull($entry->getCommentCount());
    }

    /**
     * @covers \Laminas\Feed\Writer\Entry::setEncoding
     */
    public function testSetEncodingThrowsExceptionIfNull()
    {
        $this->expectException(
            Writer\Exception\InvalidArgumentException::class
        );

        $entry = new Writer\Entry;
        $entry->setEncoding(null);
    }

    /**
     * @covers \Laminas\Feed\Writer\Entry::addCategory
     */
    public function testAddCategoryThrowsExceptionIfNotSetTerm()
    {
        $this->expectException(
            Writer\Exception\InvalidArgumentException::class
        );

        $entry = new Writer\Entry;
        $entry->addCategory(['scheme' => 'http://www.example.com/schema1']);
    }

    /**
     * @covers \Laminas\Feed\Writer\Entry::addCategory
     */
    public function testAddCategoryThrowsExceptionIfSchemeNull()
    {
        $this->expectException(
            Writer\Exception\InvalidArgumentException::class
        );

        $entry = new Writer\Entry;
        $entry->addCategory(['term' => 'cat_dog', 'scheme' => '']);
    }

    /**
     * @covers \Laminas\Feed\Writer\Entry::setEnclosure
     */
    public function testSetEnclosureThrowsExceptionIfNotSetUri()
    {
        $this->expectException(
            Writer\Exception\InvalidArgumentException::class
        );

        $entry = new Writer\Entry;
        $entry->setEnclosure(['length' => '2']);
    }

    /**
     * @covers \Laminas\Feed\Writer\Entry::setEnclosure
     */
    public function testSetEnclosureThrowsExceptionIfNotValidUri()
    {
        $this->expectException(
            Writer\Exception\InvalidArgumentException::class
        );

        $entry = new Writer\Entry;
        $entry->setEnclosure(['uri' => '']);
    }

    /**
     * @covers \Laminas\Feed\Writer\Entry::getExtension
     */
    public function testGetExtension()
    {
        $entry = new Writer\Entry;
        $foo = $entry->getExtension('foo');
        $this->assertNull($foo);

        $this->assertInstanceOf(Entry::class, $entry->getExtension('ITunes'));
    }

    /**
     * @covers \Laminas\Feed\Writer\Entry::getExtensions
     */
    public function testGetExtensions()
    {
        $entry = new Writer\Entry;

        $extensions = $entry->getExtensions();
        $this->assertInstanceOf(Entry::class, $extensions['ITunes\Entry']);
    }

    /**
     * @covers \Laminas\Feed\Writer\Entry::getSource
     * @covers \Laminas\Feed\Writer\Entry::createSource
     */
    public function testGetSource()
    {
        $entry = new Writer\Entry;

        $source = $entry->getSource();
        $this->assertNull($source);

        $entry->setSource($entry->createSource());
        $this->assertInstanceOf(Source::class, $entry->getSource());
    }

    public function testFluentInterface()
    {
        $entry = new Writer\Entry;

        $result = $entry->addAuthor(['name' => 'foo'])
                        ->addAuthors([['name' => 'foo']])
                        ->setEncoding('utf-8')
                        ->setCopyright('copyright')
                        ->setContent('content')
                        ->setDateCreated(null)
                        ->setDateModified(null)
                        ->setDescription('description')
                        ->setId('1')
                        ->setLink('http://www.example.com')
                        ->setCommentCount(1)
                        ->setCommentLink('http://www.example.com')
                        ->setCommentFeedLink(['uri' => 'http://www.example.com', 'type' => 'rss'])
                        ->setCommentFeedLinks([['uri' => 'http://www.example.com', 'type' => 'rss']])
                        ->setTitle('title')
                        ->addCategory(['term' => 'category'])
                        ->addCategories([['term' => 'category']])
                        ->setEnclosure(['uri' => 'http://www.example.com'])
                        ->setType('type')
                        ->setSource(new \Laminas\Feed\Writer\Source());

        $this->assertSame($result, $entry);
    }

    public function testSetTitleShouldAllowAStringWithTheContentsZero()
    {
        $entry = new Writer\Entry();
        $entry->setTitle('0');
        $this->assertEquals('0', $entry->getTitle());
    }

    public function testEntryWriterEmitsNoticeDuringFeedImportWhenGooglePlayPodcastExtensionUnavailable()
    {
        Writer\Writer::setExtensionManager(new TestAsset\CustomExtensionManager());

        $notices = (object) [
            'messages' => [],
        ];

        set_error_handler(function ($errno, $errstr) use ($notices) {
            $notices->messages[] = $errstr;
        }, \E_USER_NOTICE);
        $writer = new Writer\Entry();
        restore_error_handler();

        $message = array_reduce($notices->messages, function ($toReturn, $message) {
            if ('' !== $toReturn) {
                return $toReturn;
            }
            return false === strstr($message, 'GooglePlayPodcast') ? '' : $message;
        }, '');

        $this->assertNotEmpty(
            $message,
            'GooglePlayPodcast extension was present in extension manager, but was not expected to be'
        );
    }
}
