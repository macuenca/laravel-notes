<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;

class NoteTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test API tokens
     */
    const USER_A_TEST_TOKEN = 'auNb4rwZph52';
    const USER_B_TEST_TOKEN = '4HkXiQiymhZR';

    /**
     * Test user
     *
     * @var User
     */
    protected $userA;

    /**
     * Test user
     *
     * @var User
     */
    protected $userB;

    /**
     * Test note from user A
     *
     * @var Note
     */
    protected $noteA;

    /**
     * Test note from user B
     *
     * @var Note
     */
    protected $noteB;

    public function setUp()
    {
        parent::setUp();

        $this->userA = factory(App\User::class)->create([
            'name' => 'Mauricio Cuenca',
            'email' => 'mauricio@cuenca.com',
            'api_token' => self::USER_A_TEST_TOKEN,
        ]);

        $this->noteA = factory(App\Note::class)->create([
            'user_id' => $this->userA->id,
            'message' => 'Mauricio\'s note message test',
        ]);

        $this->userB = factory(App\User::class)->create([
            'name' => 'Mary Doe',
            'email' => 'mary@doe.com',
            'api_token' => self::USER_B_TEST_TOKEN,
        ]);

        $this->noteB = factory(App\Note::class)->create([
            'user_id' => $this->userB->id,
            'message' => 'Mary\'s note message test',
        ]);
    }

    /**
     * Test getting the notes owned by each user.
     */
    public function testGetNotesForAuthenticatedUser()
    {
        $this->actingAs($this->userA, 'api');
        $this->json('GET', sprintf('/api/v1/notes?api_token=%s', self::USER_A_TEST_TOKEN))
            ->seeJson([
                'id' => $this->userA->id,
                'message' => 'Mauricio\'s note message test',
            ]);

        $this->actingAs($this->userB, 'api');
        $this->json('GET', sprintf('/api/v1/notes?api_token=%s', self::USER_B_TEST_TOKEN))
            ->seeJson([
                'id' => $this->userB->id,
                'message' => 'Mary\'s note message test',
            ]);
    }

    /**
     * Test getting a specific note ID owned by the authenticated user.
     */
    public function testGetNoteById()
    {
        $this->actingAs($this->userA, 'api');
        $this->json('GET', sprintf('/api/v1/notes/%d?api_token=%s', $this->noteA->id, self::USER_A_TEST_TOKEN))
            ->seeJson([
                'id' => $this->userA->id,
                'message' => 'Mauricio\'s note message test',
            ]);
    }

    /**
     * Test getting a specific note ID owned by a different user.
     */
    public function testGetUnauthorizedNoteById()
    {
        // User A trying to retrieve user's B note should throw an 404 HTTP response
        $this->actingAs($this->userA, 'api');
        $response = $this->call('GET', sprintf('/api/v1/notes/%d', $this->noteB->id), ['api_token' => self::USER_A_TEST_TOKEN]);
        $this->assertEquals(Illuminate\Http\Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    /**
     * Test the tags are adhered to the posted note
     */
    public function testCreateNoteWithTags()
    {
        $this->json(
            'POST',
            sprintf('/api/v1/notes/?api_token=%s', self::USER_A_TEST_TOKEN),
            [
                'message' => 'Adding a new note to my notes.',
                'tags' => 'task, non-urgent, reminder',
            ])
            ->seeJson([
                'message' => 'Adding a new note to my notes.',
                'name' => 'task',
                'name' => 'non-urgent',
                'name' => 'reminder',
            ]);
    }

    /**
     * Test whether a note can be edited
     */
    public function testEditNote()
    {
        $this->actingAs($this->userA, 'api');
        $this->json(
            'PUT',
            sprintf('/api/v1/notes/1?api_token=%s', self::USER_A_TEST_TOKEN),
            [
                'message' => 'Modifying the message in the note.',
                'tags' => 'test, notes',
            ])
            ->seeJson([
                'message' => 'Modifying the message in the note.',
                'name' => 'test',
                'name' => 'notes',
            ]);
    }

    /**
     * Test whether a note can be deleted
     */
    public function testDeleteNote()
    {
        // Removing the only existing note and expecting an empty response
        $response = $this->call(
            'DELETE',
            sprintf('/api/v1/notes/%d', $this->noteA->id),
            [
                'api_token' => self::USER_A_TEST_TOKEN
            ]
        );
        $this->assertEmpty(json_decode($response->getContent()));
    }
}
