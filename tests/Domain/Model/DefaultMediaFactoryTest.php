<?php

declare(strict_types=1);

namespace Damax\Media\Tests\Domain\Model;

use Damax\Media\Domain\Model\DefaultMediaFactory;
use Damax\Media\Domain\Model\MediaId;
use Damax\Media\Domain\Model\UserId;
use PHPUnit\Framework\TestCase;

class DefaultMediaFactoryTest extends TestCase
{
    /**
     * @var DefaultMediaFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new DefaultMediaFactory();
    }

    /**
     * @test
     */
    public function it_creates_media()
    {
        $media = $this->factory->create([
            'id' => MediaId::fromString('183702c5-30de-11e8-97f3-005056806fb2'),
            'type' => 'document',
            'name' => 'Test PDF document',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'user_id' => UserId::fromString('04907d72-9c88-11e8-add5-0242ac110004'),
        ]);

        $this->assertEquals('183702c5-30de-11e8-97f3-005056806fb2', (string) $media->id());
        $this->assertEquals('pending', $media->status());
        $this->assertEquals('document', $media->type());
        $this->assertEquals('Test PDF document', $media->name());
        $this->assertEquals('application/pdf', $media->info()->mimeType());
        $this->assertEquals(1024, $media->info()->fileSize());
        $this->assertEquals([], $media->metadata()->all());
        $this->assertEquals('04907d72-9c88-11e8-add5-0242ac110004', (string) $media->createdById());
        $this->assertEquals('04907d72-9c88-11e8-add5-0242ac110004', (string) $media->updatedById());
        $this->assertFalse($media->uploaded());
    }
}
