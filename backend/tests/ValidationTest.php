<?php
/**
 * Unit tests for input validation functions in utils/validation.php.
 */

use PHPUnit\Framework\TestCase;

final class ValidationTest extends TestCase
{
    public function testValidateUsername_AllowsValidUsernames(): void
    {
        $this->assertSame(1, validate_username('alice'));
        $this->assertSame(1, validate_username('foo_bar'));
        $this->assertSame(1, validate_username('john.doe'));
        $this->assertSame(1, validate_username('A1_b.C'));
    }

    public function testValidateUsername_RejectsInvalidUsernames(): void
    {
        $this->assertSame(0, validate_username('ab')); // Too short
        $this->assertSame(
            0,
            validate_username('thisusernameiswaytoolongtobevalid'),
        );
        $this->assertSame(0, validate_username('has space'));
        $this->assertSame(0, validate_username('bad!chars'));
    }

    public function testValidatePassword_AllowsStrongPasswords(): void
    {
        $this->assertSame(1, validate_password('S3cur3!Pass'));
        $this->assertSame(1, validate_password('Some$tr0ngP@ssw0rd'));
    }

    public function testValidatePassword_RejectsWeakPasswords(): void
    {
        $this->assertSame(0, validate_password('short'));
        $this->assertSame(0, validate_password('alllowercase1!'));
        $this->assertSame(0, validate_password('ALLUPPERCASE1!'));
        $this->assertSame(0, validate_password('NoNumbers!!'));
        $this->assertSame(0, validate_password('NoSpecialChar1'));
    }
}
