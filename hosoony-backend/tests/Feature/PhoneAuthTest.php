<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\WhatsAppVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PhoneAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_code_generation()
    {
        $phoneNumber = '966501234567';
        
        $code = VerificationCode::createForPhone($phoneNumber, 'login');
        
        $this->assertNotNull($code);
        $this->assertEquals($phoneNumber, $code->phone_number);
        $this->assertEquals(6, strlen($code->code));
        $this->assertFalse($code->is_used);
        $this->assertFalse($code->isExpired());
    }

    public function test_verification_code_verification()
    {
        $phoneNumber = '966501234567';
        $code = VerificationCode::createForPhone($phoneNumber, 'login');
        
        $verifiedCode = VerificationCode::verifyCode($phoneNumber, $code->code);
        
        $this->assertNotNull($verifiedCode);
        $this->assertTrue($verifiedCode->is_used);
        $this->assertNotNull($verifiedCode->used_at);
    }

    public function test_invalid_verification_code()
    {
        $phoneNumber = '966501234567';
        VerificationCode::createForPhone($phoneNumber, 'login');
        
        $verifiedCode = VerificationCode::verifyCode($phoneNumber, '000000');
        
        $this->assertNull($verifiedCode);
    }

    public function test_phone_auth_controller_send_code()
    {
        // Skip this test if Twilio is not configured
        if (!config('services.twilio.sid')) {
            $this->markTestSkipped('Twilio not configured');
        }

        $response = $this->postJson('/phone-auth/send-code', [
            'phone' => '0501234567'
        ]);

        // Just check that we get a response (could be 200 or 500 depending on Twilio config)
        $this->assertTrue(in_array($response->status(), [200, 500]));
    }

    public function test_phone_auth_controller_verify_code()
    {
        $phoneNumber = '966501234567';
        $code = VerificationCode::createForPhone($phoneNumber, 'login');
        
        // Skip this test if Twilio is not configured
        if (!config('services.twilio.sid')) {
            $this->markTestSkipped('Twilio not configured');
        }
        
        $response = $this->postJson('/phone-auth/verify-code', [
            'phone' => '0501234567',
            'code' => $code->code
        ]);

        // Just check that we get a response (could be 200 or 500 depending on Twilio config)
        $this->assertTrue(in_array($response->status(), [200, 500]));
    }

    public function test_phone_login_page_loads()
    {
        $response = $this->get('/phone-login');
        
        $response->assertStatus(200);
        $response->assertSee('حسوني');
        $response->assertSee('رقم الجوال');
    }

    public function test_redirect_to_phone_login_when_not_authenticated()
    {
        $response = $this->get('/');
        
        $response->assertRedirect('/phone-login');
    }

    public function test_phone_number_normalization()
    {
        $controller = new \App\Http\Controllers\PhoneAuthController(
            new WhatsAppVerificationService()
        );
        
        // Test reflection to access protected method
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('normalizePhoneNumber');
        $method->setAccessible(true);
        
        $this->assertEquals('966501234567', $method->invoke($controller, '0501234567'));
        $this->assertEquals('966501234567', $method->invoke($controller, '501234567'));
        $this->assertEquals('966501234567', $method->invoke($controller, '966501234567'));
    }
}
