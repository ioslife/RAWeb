<?php

use App\Models\Achievement;
use App\Models\Comment;
use App\Models\Game;
use App\Models\System;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\Feature\Api\V1\BootstrapsApiV1;
use Tests\TestCase;

class API_GetCommentsTest extends TestCase
{
    use RefreshDatabase; use WithFaker;
    use BootstrapsApiV1;

    public function testItValidates(): void
    {
        $this->get($this->apiUrl('GetComments', ['u' => '1', 't' => 1]))
            ->assertJsonValidationErrors([
                'u',
            ]);
    }

    public function testGetCommentsUnknownUser(): void
    {
        $this->get($this->apiUrl('GetComments', ['u' => 'nonExistant', 't' => 3]))
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function testGetCommentsForAchievement(): void
    {
        // Arrange
        $system = System::factory()->create();
        $game = Game::factory()->create(['ConsoleID' => $system->ID]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $bannedUser = User::factory()->create(['banned_at' => Carbon::now()]);

        debug_to_console($bannedUser);

        $achievement = Achievement::factory()->create(['GameID' => $game->ID, 'user_id' => $user1->ID]);
        $comment1 = Comment::factory()->create([
            'ArticleID' => $achievement->ID,
            'ArticleType' => 2,
            'user_id' => $user1->ID,
            'Payload' => 'This is a great achievement!',
        ]);
        $comment2 = Comment::factory()->create([
            'ArticleID' => $achievement->ID,
            'ArticleType' => 2,
            'user_id' => $user2->ID,
            'Payload' => 'I agree, this is awesome!',
        ]);
        $comment3 = Comment::factory()->create([
            'ArticleID' => $achievement->ID,
            'ArticleType' => 2,
            'user_id' => $bannedUser->ID,
            'Payload' => 'This comment is from a banned user!',
        ]);

        // Act
        $response = $this->get($this->apiUrl('GetComments', ['i' => $achievement->ID, 't' => 2]))
            ->assertSuccessful();

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'Count',
            'Total',
            'Results' => [
                '*' => [
                    'User',
                    'Submitted',
                    'CommentText',
                ],
            ],
        ]);
        $this->assertCount(2, $response->json('Results'));
        $this->assertEquals($user1->User, $response->json('Results.0.User'));
        $this->assertEquals($comment1->Payload, $response->json('Results.0.CommentText'));
        $this->assertEquals($user2->User, $response->json('Results.1.User'));
        $this->assertEquals($comment2->Payload, $response->json('Results.1.CommentText'));
    }

    public function testGetCommentsForGame(): void
    {
        // Arrange
        $system = System::factory()->create();
        $game = Game::factory()->create(['ConsoleID' => $system->ID]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $bannedUser = User::factory()->create(['banned_at' => Carbon::now()]);

        $comment1 = Comment::factory()->create([
            'ArticleID' => $game->ID,
            'ArticleType' => 1,
            'user_id' => $user1->ID,
            'Payload' => 'This is a great achievement!',
        ]);
        $comment2 = Comment::factory()->create([
            'ArticleID' => $game->ID,
            'ArticleType' => 1,
            'user_id' => $user2->ID,
            'Payload' => 'I agree, this is awesome!',
        ]);
        $comment3 = Comment::factory()->create([
            'ArticleID' => $achievement->ID,
            'ArticleType' => 2,
            'user_id' => $bannedUser->ID,
            'Payload' => 'This comment is from a banned user!',
        ]);

        // Act
        $response = $this->get($this->apiUrl('GetComments', ['i' => $game->ID, 't' => 1]))
            ->assertSuccessful();

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'Count',
            'Total',
            'Results' => [
                '*' => [
                    'User',
                    'Submitted',
                    'CommentText',
                ],
            ],
        ]);
        $this->assertCount(2, $response->json('Results'));
        $this->assertEquals($user1->username, $response->json('Results.0.User'));
        $this->assertEquals($comment1->Payload, $response->json('Results.0.CommentText'));
        $this->assertEquals($user2->username, $response->json('Results.1.User'));
        $this->assertEquals($comment2->Payload, $response->json('Results.1.CommentText'));
    }

    public function testGetCommentsForUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $bannedUser = User::factory()->create(['banned_at' => Carbon::now()]);

        $comment1 = Comment::factory()->create([
            'ArticleID' => $user->ID,
            'ArticleType' => 3,
            'user_id' => $user2->ID,
            'Payload' => 'This is my first comment.',
        ]);
        $comment2 = Comment::factory()->create([
            'ArticleID' => $user->ID,
            'ArticleType' => 3,
            'user_id' => $user2->ID,
            'Payload' => 'This is my second comment.',
        ]);
        $comment3 = Comment::factory()->create([
            'ArticleID' => $achievement->ID,
            'ArticleType' => 2,
            'user_id' => $bannedUser->ID,
            'Payload' => 'This comment is from a banned user!',
        ]);

        // Act
        $response = $this->get($this->apiUrl('GetComments', ['u' => $user->User, 't' => 3]))
            ->assertSuccessful();

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'Count',
            'Total',
            'Results' => [
                '*' => [
                    'User',
                    'Submitted',
                    'CommentText',
                ],
            ],
        ]);
        $this->assertCount(2, $response->json('Results'));
        $this->assertEquals($user2->User, $response->json('Results.0.User'));
        $this->assertEquals($comment1->Payload, $response->json('Results.0.CommentText'));
        $this->assertEquals($user2->User, $response->json('Results.1.User'));
        $this->assertEquals($comment2->Payload, $response->json('Results.1.CommentText'));
    }

    public function testGetCommentsForUserWithDisabledWall(): void
    {
        // Arrange
        $user = User::factory()->create(['UserWallActive' => false]);
        $user2 = User::factory()->create();
        $comment1 = Comment::factory()->create([
            'ArticleID' => $user->ID,
            'ArticleType' => 3,
            'user_id' => $user2->ID,
            'Payload' => 'This is my first comment.',
        ]);
        $comment2 = Comment::factory()->create([
            'ArticleID' => $user->ID,
            'ArticleType' => 3,
            'user_id' => $user2->ID,
            'Payload' => 'This is my second comment.',
        ]);

        debug_to_console($user);

        // Act
        $response = $this->get($this->apiUrl('GetComments', ['u' => $user->User, 't' => 3]))
            ->assertSuccessful();

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([]);
    }
}

function debug_to_console($data)
{
    $output = $data;
    if (is_array($output)) {
        $output = implode(',', $output);
    }

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}