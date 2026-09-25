<?php

namespace Wink\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Wink\WinkPage;
use Wink\WinkPost;
use Wink\WinkTag;

class PublishingTest extends TestCase
{
    public function test_markdown_posts_render_html_and_scopes_filter_them(): void
    {
        $author = $this->author();

        $live = $this->postFor($author, [
            'markdown' => true,
            'body' => '**Hello**',
            'published' => true,
            'publish_date' => now()->subDay(),
        ]);
        $draft = $this->postFor($author, [
            'id' => (string) Str::uuid(),
            'slug' => 'draft',
            'published' => false,
            'publish_date' => now()->subDay(),
        ]);
        $scheduled = $this->postFor($author, [
            'id' => (string) Str::uuid(),
            'slug' => 'scheduled',
            'published' => true,
            'publish_date' => now()->addDay(),
        ]);

        $tag = WinkTag::create([
            'id' => (string) Str::uuid(),
            'slug' => 'laravel',
            'name' => 'Laravel',
        ]);
        $live->tags()->attach($tag->id);

        $this->assertStringContainsString('<strong>Hello</strong>', (string) $live->content);
        $this->assertSame('The body', $draft->content);
        $this->assertSame([$live->id], WinkPost::live()->pluck('id')->all());
        $this->assertSame([$draft->id], WinkPost::draft()->pluck('id')->all());
        $this->assertSame([$scheduled->id], WinkPost::scheduled()->pluck('id')->all());
        $this->assertTrue(WinkPost::tag('laravel')->whereKey($live->id)->exists());
    }

    public function test_authors_can_manage_posts_pages_tags_and_team_members(): void
    {
        $author = $this->author();
        $this->actingAs($author, 'wink');

        $this->postJson('/wink/api/posts/new', [
            'id' => $postId = (string) Str::uuid(),
            'title' => 'First post',
            'slug' => 'first-post',
            'excerpt' => 'Excerpt',
            'body' => 'Body',
            'published' => true,
            'markdown' => false,
            'author_id' => $author->id,
            'publish_date' => now()->format('Y-m-d H:i:s'),
            'tags' => [
                ['id' => (string) Str::uuid(), 'name' => 'News'],
            ],
        ])->assertOk()->assertJsonPath('entry.slug', 'first-post');

        $this->assertTrue(WinkPost::find($postId)->tags->contains('name', 'News'));

        $this->postJson('/wink/api/posts/new', [
            'id' => (string) Str::uuid(),
            'title' => 'Duplicate',
            'slug' => 'first-post',
            'author_id' => $author->id,
            'publish_date' => now()->format('Y-m-d H:i:s'),
        ])->assertStatus(422);

        $this->getJson('/wink/api/posts?status=published&author_id='.$author->id)
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'first-post');

        $this->postJson('/wink/api/pages/new', [
            'id' => $pageId = (string) Str::uuid(),
            'title' => 'About',
            'slug' => 'about',
            'body' => 'About this site',
        ])->assertOk();

        $this->assertSame('About this site', WinkPage::find($pageId)->content);

        $this->postJson('/wink/api/tags/new', [
            'id' => $tagId = (string) Str::uuid(),
            'name' => 'Laravel',
            'slug' => 'laravel',
        ])->assertOk();

        $this->deleteJson('/wink/api/tags/'.$tagId)->assertOk();
        $this->assertNull(WinkTag::find($tagId));

        $teammateId = (string) Str::uuid();

        $this->postJson('/wink/api/team/new', [
            'id' => $teammateId,
            'name' => 'Ada Lovelace',
            'slug' => 'ada-lovelace',
            'email' => 'ada@example.com',
            'password' => 'secret-pass',
            'bio' => 'Author',
        ])->assertOk();

        $this->deleteJson('/wink/api/team/'.$author->id)
            ->assertStatus(402)
            ->assertJsonPath('message', 'Please remove the author\'s posts first.');

        $this->deleteJson('/wink/api/posts/'.$postId)->assertOk();

        $this->deleteJson('/wink/api/team/'.$author->id)
            ->assertStatus(402)
            ->assertJsonPath('message', 'You cannot delete yourself.');

        $this->deleteJson('/wink/api/team/'.$teammateId)->assertOk();
        $this->assertNull(\Wink\WinkAuthor::find($teammateId));
    }

    public function test_image_uploads_are_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        $this->actingAs($this->author(), 'wink');

        $response = $this->postJson('/wink/api/uploads', [
            'image' => UploadedFile::fake()->create('photo.jpg', 20, 'image/jpeg'),
        ]);

        $response->assertOk();

        $url = $response->json('url');

        $this->assertIsString($url);
        $this->assertStringContainsString('wink/images', $url);

        $stored = Storage::disk('public')->allFiles('wink/images');

        $this->assertCount(1, $stored);
    }
}
