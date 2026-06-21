<?php
namespace Tests;

use App\Models\User;
use App\Models\Child;
use App\Models\Message;
use App\Models\Appointment;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\Activity;
use App\Models\Subscription;

class ModelTest extends TestRunner
{
    private static int $testUserId;
    private static int $testSpecialistId;

    public static function setUpBeforeClass(): void
    {
        $db = self::getDb();
        $stmt = $db->prepare("DELETE FROM users WHERE email LIKE 'testmodel%@test.com'");
        $stmt->execute();

        self::$testUserId = User::create([
            'role' => 'parent',
            'name' => 'Model Test Parent',
            'email' => 'testmodel_parent_' . time() . '@test.com',
            'password' => password_hash('test1234', PASSWORD_BCRYPT),
        ]);

        self::$testSpecialistId = User::create([
            'role' => 'specialist',
            'name' => 'Model Test Specialist',
            'email' => 'testmodel_spec_' . time() . '@test.com',
            'password' => password_hash('test1234', PASSWORD_BCRYPT),
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        $db = self::getDb();
        $db->prepare('DELETE FROM users WHERE id = ?')->execute([self::$testUserId]);
        $db->prepare('DELETE FROM users WHERE id = ?')->execute([self::$testSpecialistId]);
    }

    public function testUserCreateAndFindById()
    {
        $user = User::findById(self::$testUserId);
        $this->assertNotNull($user);
        $this->assertEquals('Model Test Parent', $user['name']);
        $this->assertEquals('parent', $user['role']);
    }

    public function testUserFindByEmail()
    {
        $user = User::findByEmail('admin@autimind.com');
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user['role']);
    }

    public function testUserFindByEmailReturnsFalseForNonExistent()
    {
        $user = User::findByEmail('nonexistent_' . time() . '@test.com');
        $this->assertFalse($user);
    }

    public function testUserGetAllByRole()
    {
        $parents = User::getAllByRole('parent');
        $this->assertIsArray($parents);
        $this->assertNotEmpty($parents);
        foreach ($parents as $p) {
            $this->assertEquals('parent', $p['role']);
            $this->assertEquals(1, $p['is_active']);
        }
    }

    public function testUserCountByRole()
    {
        $count = User::countByRole('admin');
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testUserUpdatePassword()
    {
        $newHash = password_hash('newpassword123', PASSWORD_BCRYPT);
        User::updatePassword(self::$testUserId, $newHash);
        $user = User::findById(self::$testUserId);
        $this->assertTrue(password_verify('newpassword123', $user['password']));

        User::updatePassword(self::$testUserId, password_hash('test1234', PASSWORD_BCRYPT));
    }

    public function testChildCreateAndFind()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId,
            'name' => 'Test Child Model',
            'age' => 6,
            'birth_date' => '2020-01-15',
            'diagnosis_status' => 'suspected',
            'notes' => 'Test notes',
        ]);
        $this->assertGreaterThan(0, $childId);

        $child = Child::findById($childId);
        $this->assertNotNull($child);
        $this->assertEquals('Test Child Model', $child['name']);
        $this->assertEquals(6, $child['age']);
        $this->assertEquals(self::$testUserId, $child['parent_id']);

        Child::delete($childId);
        $this->assertFalse(Child::findById($childId));
    }

    public function testChildBelongsToParent()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId,
            'name' => 'Belongs Test',
            'age' => 3,
        ]);

        $this->assertTrue(Child::belongsToParent($childId, self::$testUserId));
        $this->assertFalse(Child::belongsToParent($childId, 99999));

        Child::delete($childId);
    }

    public function testChildUpdate()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId, 'name' => 'Update Test', 'age' => 4,
        ]);

        Child::update($childId, [
            'name' => 'Updated Name', 'age' => 5, 'diagnosis_status' => 'confirmed',
        ]);

        $child = Child::findById($childId);
        $this->assertEquals('Updated Name', $child['name']);
        $this->assertEquals(5, $child['age']);
        $this->assertEquals('confirmed', $child['diagnosis_status']);

        Child::delete($childId);
    }

    public function testChildGetAllByParent()
    {
        $c1 = Child::create(['parent_id' => self::$testUserId, 'name' => 'Child A', 'age' => 3]);
        $c2 = Child::create(['parent_id' => self::$testUserId, 'name' => 'Child B', 'age' => 5]);

        $children = Child::getAllByParent(self::$testUserId);
        $names = array_column($children, 'name');
        $this->assertContains('Child A', $names);
        $this->assertContains('Child B', $names);

        Child::delete($c1);
        Child::delete($c2);
    }

    public function testChildCountByParent()
    {
        $before = Child::countByParent(self::$testUserId);
        $c1 = Child::create(['parent_id' => self::$testUserId, 'name' => 'Count Test', 'age' => 3]);
        $after = Child::countByParent(self::$testUserId);
        $this->assertEquals($before + 1, $after);
        Child::delete($c1);
    }

    public function testMessageSendAndFind()
    {
        $msgId = Message::send([
            'sender_id' => self::$testUserId,
            'receiver_id' => self::$testSpecialistId,
            'subject' => 'Test Subject',
            'body' => 'Test message body content',
        ]);
        $this->assertGreaterThan(0, $msgId);

        $msg = Message::findById($msgId);
        $this->assertNotNull($msg);
        $this->assertEquals('Test Subject', $msg['subject']);
        $this->assertEquals('Test message body content', $msg['body']);

        $db = self::getDb();
        $db->prepare('DELETE FROM messages WHERE id = ?')->execute([$msgId]);
    }

    public function testMessageGetInbox()
    {
        $msgId = Message::send([
            'sender_id' => self::$testSpecialistId,
            'receiver_id' => self::$testUserId,
            'subject' => 'Inbox Test',
            'body' => 'Incoming message',
        ]);

        $inbox = Message::getInbox(self::$testUserId);
        $subjects = array_column($inbox, 'subject');
        $this->assertContains('Inbox Test', $subjects);

        $db = self::getDb();
        $db->prepare('DELETE FROM messages WHERE id = ?')->execute([$msgId]);
    }

    public function testMessageGetSent()
    {
        $msgId = Message::send([
            'sender_id' => self::$testUserId,
            'receiver_id' => self::$testSpecialistId,
            'subject' => 'Sent Test',
            'body' => 'Outgoing message',
        ]);

        $sent = Message::getSent(self::$testUserId);
        $subjects = array_column($sent, 'subject');
        $this->assertContains('Sent Test', $subjects);

        $db = self::getDb();
        $db->prepare('DELETE FROM messages WHERE id = ?')->execute([$msgId]);
    }

    public function testMessageGetThread()
    {
        $m1 = Message::send([
            'sender_id' => self::$testUserId,
            'receiver_id' => self::$testSpecialistId,
            'subject' => 'Thread Msg 1',
            'body' => 'First',
        ]);
        $m2 = Message::send([
            'sender_id' => self::$testSpecialistId,
            'receiver_id' => self::$testUserId,
            'subject' => 'Thread Msg 2',
            'body' => 'Reply',
        ]);

        $thread = Message::getThread(self::$testUserId, self::$testSpecialistId);
        $this->assertCount(2, $thread);
        $this->assertEquals('First', $thread[0]['body']);

        $db = self::getDb();
        $db->prepare('DELETE FROM messages WHERE id IN (?, ?)')->execute([$m1, $m2]);
    }

    public function testMessageMarkAsRead()
    {
        $msgId = Message::send([
            'sender_id' => self::$testSpecialistId,
            'receiver_id' => self::$testUserId,
            'subject' => 'Read Test',
            'body' => 'Read me',
        ]);

        $msg = Message::findById($msgId);
        $this->assertEquals(0, $msg['is_read']);

        Message::markAsRead($msgId);
        $msg = Message::findById($msgId);
        $this->assertEquals(1, $msg['is_read']);

        $db = self::getDb();
        $db->prepare('DELETE FROM messages WHERE id = ?')->execute([$msgId]);
    }

    public function testMessageCountUnread()
    {
        $before = Message::countUnread(self::$testUserId);

        $msgId = Message::send([
            'sender_id' => self::$testSpecialistId,
            'receiver_id' => self::$testUserId,
            'subject' => 'Unread Count',
            'body' => 'Unread message',
        ]);

        $after = Message::countUnread(self::$testUserId);
        $this->assertEquals($before + 1, $after);

        Message::markAsRead($msgId);
        $afterRead = Message::countUnread(self::$testUserId);
        $this->assertEquals($before, $afterRead);

        $db = self::getDb();
        $db->prepare('DELETE FROM messages WHERE id = ?')->execute([$msgId]);
    }

    public function testMessageGetConversationPartners()
    {
        Message::send([
            'sender_id' => self::$testUserId,
            'receiver_id' => self::$testSpecialistId,
            'subject' => 'Partner Test',
            'body' => 'Hello',
        ]);

        $partners = Message::getConversationPartners(self::$testUserId);
        $ids = array_column($partners, 'id');
        $this->assertContains(self::$testSpecialistId, $ids);

        $db = self::getDb();
        $db->prepare('DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?')
            ->execute([self::$testUserId, self::$testUserId]);
    }

    public function testAppointmentCreateAndFind()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId, 'name' => 'Apt Child', 'age' => 5,
        ]);

        $aptId = Appointment::create([
            'child_id' => $childId,
            'specialist_id' => self::$testSpecialistId,
            'parent_id' => self::$testUserId,
            'date' => '2026-08-15',
            'time' => '14:00:00',
            'duration' => 45,
            'notes' => 'Test appointment',
        ]);
        $this->assertGreaterThan(0, $aptId);

        $apt = Appointment::findById($aptId);
        $this->assertNotNull($apt);
        $this->assertEquals('pending', $apt['status']);
        $this->assertEquals('2026-08-15', $apt['date']);

        Appointment::updateStatus($aptId, 'confirmed');
        $apt = Appointment::findById($aptId);
        $this->assertEquals('confirmed', $apt['status']);

        $db = self::getDb();
        $db->prepare('DELETE FROM appointments WHERE id = ?')->execute([$aptId]);
        Child::delete($childId);
    }

    public function testAppointmentGetAllByParent()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId, 'name' => 'Apt List Child', 'age' => 4,
        ]);
        $aptId = Appointment::create([
            'child_id' => $childId, 'specialist_id' => self::$testSpecialistId,
            'parent_id' => self::$testUserId, 'date' => '2026-09-01', 'time' => '10:00',
        ]);

        $appointments = Appointment::getAllByParent(self::$testUserId);
        $this->assertNotEmpty($appointments);
        $this->assertEquals('Apt List Child', $appointments[0]['child_name']);

        $db = self::getDb();
        $db->prepare('DELETE FROM appointments WHERE id = ?')->execute([$aptId]);
        Child::delete($childId);
    }

    public function testAppointmentGetAllBySpecialist()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId, 'name' => 'Spec Apt', 'age' => 3,
        ]);
        $aptId = Appointment::create([
            'child_id' => $childId, 'specialist_id' => self::$testSpecialistId,
            'parent_id' => self::$testUserId, 'date' => '2026-10-01', 'time' => '11:00',
        ]);

        $appointments = Appointment::getAllBySpecialist(self::$testSpecialistId);
        $this->assertNotEmpty($appointments);

        $db = self::getDb();
        $db->prepare('DELETE FROM appointments WHERE id = ?')->execute([$aptId]);
        Child::delete($childId);
    }

    public function testAppointmentCountByParent()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId, 'name' => 'Count Child', 'age' => 6,
        ]);

        $before = Appointment::countByParent(self::$testUserId);
        $aptId = Appointment::create([
            'child_id' => $childId, 'specialist_id' => self::$testSpecialistId,
            'parent_id' => self::$testUserId, 'date' => '2026-11-01', 'time' => '09:00',
        ]);
        $after = Appointment::countByParent(self::$testUserId);
        $this->assertEquals($before + 1, $after);

        $pending = Appointment::countByParent(self::$testUserId, 'pending');
        $this->assertGreaterThanOrEqual(1, $pending);

        $db = self::getDb();
        $db->prepare('DELETE FROM appointments WHERE id = ?')->execute([$aptId]);
        Child::delete($childId);
    }

    public function testAppointmentGetUpcomingByParent()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId, 'name' => 'Upcoming Child', 'age' => 5,
        ]);
        $aptId = Appointment::create([
            'child_id' => $childId, 'specialist_id' => self::$testSpecialistId,
            'parent_id' => self::$testUserId, 'date' => date('Y-m-d', strtotime('+30 days')),
            'time' => '10:00',
        ]);

        $upcoming = Appointment::getUpcomingByParent(self::$testUserId);
        $this->assertNotEmpty($upcoming);

        $db = self::getDb();
        $db->prepare('DELETE FROM appointments WHERE id = ?')->execute([$aptId]);
        Child::delete($childId);
    }

    public function testQuizQuestionGetAllActive()
    {
        $questions = QuizQuestion::getAllActive();
        $this->assertNotEmpty($questions);
        $this->assertCount(10, $questions);

        foreach ($questions as $q) {
            $this->assertArrayHasKey('options', $q);
            $this->assertNotEmpty($q['options']);
            $this->assertCount(6, $q['options']);
        }
    }

    public function testQuizQuestionCategories()
    {
        $questions = QuizQuestion::getAllActive();
        $categories = array_unique(array_column($questions, 'category'));
        $expected = ['social_communication', 'behavior', 'sensory', 'developmental'];
        foreach ($expected as $cat) {
            $this->assertContains($cat, $categories);
        }
    }

    public function testQuizAttemptCreateAndComplete()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId, 'name' => 'Quiz Attempt Child', 'age' => 5,
        ]);

        $attemptId = QuizAttempt::create($childId);
        $this->assertGreaterThan(0, $attemptId);

        $attempt = QuizAttempt::findById($attemptId);
        $this->assertEquals('in_progress', $attempt['status']);

        QuizAttempt::complete($attemptId, 25, 'moderate');
        $attempt = QuizAttempt::findById($attemptId);
        $this->assertEquals('completed', $attempt['status']);
        $this->assertEquals(25, $attempt['total_score']);
        $this->assertEquals('moderate', $attempt['risk_level']);

        QuizAttempt::delete($attemptId);
        Child::delete($childId);
    }

    public function testQuizAttemptGetByChild()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId, 'name' => 'Quiz History Child', 'age' => 4,
        ]);
        $a1 = QuizAttempt::create($childId);
        QuizAttempt::complete($a1, 10, 'low');
        $a2 = QuizAttempt::create($childId);
        QuizAttempt::complete($a2, 35, 'high');

        $attempts = QuizAttempt::getByChild($childId);
        $this->assertCount(2, $attempts);

        QuizAttempt::delete($a2);
        QuizAttempt::delete($a1);
        Child::delete($childId);
    }

    public function testQuizAttemptGetLatestByChild()
    {
        $childId = Child::create([
            'parent_id' => self::$testUserId, 'name' => 'Latest Quiz', 'age' => 6,
        ]);
        $a1 = QuizAttempt::create($childId);
        QuizAttempt::complete($a1, 10, 'low');
        sleep(1);
        $a2 = QuizAttempt::create($childId);
        QuizAttempt::complete($a2, 40, 'high');

        $latest = QuizAttempt::getLatestByChild($childId);
        $this->assertEquals(40, $latest['total_score']);
        $this->assertEquals('high', $latest['risk_level']);

        QuizAttempt::delete($a2);
        QuizAttempt::delete($a1);
        Child::delete($childId);
    }

    public function testActivityCreateUpdateDelete()
    {
        $id = Activity::create([
            'title' => 'Test Game',
            'description' => 'A test activity',
            'category' => 'games',
            'difficulty' => 'easy',
        ]);
        $this->assertGreaterThan(0, $id);

        $activity = Activity::findById($id);
        $this->assertEquals('Test Game', $activity['title']);
        $this->assertEquals('games', $activity['category']);

        Activity::update($id, [
            'title' => 'Updated Game',
            'category' => 'puzzles',
            'difficulty' => 'medium',
        ]);
        $activity = Activity::findById($id);
        $this->assertEquals('Updated Game', $activity['title']);

        Activity::delete($id);
        $this->assertFalse(Activity::findById($id));
    }

    public function testActivityGetAllActive()
    {
        $all = Activity::getAllActive();
        $this->assertIsArray($all);
        foreach ($all as $a) {
            $this->assertEquals(1, $a['is_active']);
        }
    }

    public function testSubscriptionCreateDelete()
    {
        $id = Subscription::create([
            'user_id' => self::$testUserId,
            'plan' => 'premium',
            'status' => 'active',
            'ends_at' => date('Y-m-d', strtotime('+1 year')),
        ]);
        $this->assertGreaterThan(0, $id);

        Subscription::delete($id);
        $db = self::getDb();
        $stmt = $db->prepare('SELECT COUNT(*) FROM subscriptions WHERE id = ?');
        $stmt->execute([$id]);
        $this->assertEquals(0, (int)$stmt->fetchColumn());
    }

    public function testSubscriptionCount()
    {
        $total = Subscription::count();
        $this->assertGreaterThanOrEqual(0, $total);

        $active = Subscription::countActive();
        $this->assertGreaterThanOrEqual(0, $active);
    }

    public function testSubscriptionGetByUser()
    {
        $id = Subscription::create([
            'user_id' => self::$testUserId,
            'plan' => 'standard',
            'status' => 'active',
        ]);

        $sub = Subscription::getByUser(self::$testUserId);
        $this->assertNotNull($sub);
        $this->assertEquals('standard', $sub['plan']);

        Subscription::delete($id);
    }

    public function testSubscriptionGetAll()
    {
        $all = Subscription::getAll();
        $this->assertIsArray($all);
    }

    public function testSubscriptionUpdate()
    {
        $id = Subscription::create([
            'user_id' => self::$testUserId,
            'plan' => 'standard',
            'status' => 'active',
        ]);

        Subscription::update($id, [
            'plan' => 'premium',
            'status' => 'active',
            'ends_at' => null,
        ]);

        $db = self::getDb();
        $stmt = $db->prepare('SELECT * FROM subscriptions WHERE id = ?');
        $stmt->execute([$id]);
        $sub = $stmt->fetch();
        $this->assertEquals('premium', $sub['plan']);

        Subscription::delete($id);
    }

    public function testQuizQuestionCreateUpdateDelete()
    {
        $qId = QuizQuestion::create([
            'question_text' => 'Test question?',
            'category' => 'behavior',
            'order_index' => 99,
        ]);
        $this->assertGreaterThan(0, $qId);

        $question = QuizQuestion::findById($qId);
        $this->assertEquals('Test question?', $question['question_text']);

        QuizQuestion::update($qId, [
            'question_text' => 'Updated question?',
            'category' => 'sensory',
            'order_index' => 99,
        ]);
        $question = QuizQuestion::findById($qId);
        $this->assertEquals('Updated question?', $question['question_text']);

        QuizQuestion::delete($qId);
        $this->assertFalse(QuizQuestion::findById($qId));
    }
}
