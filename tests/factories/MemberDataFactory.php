<?php
class MemberDataFactory
{
    public static function testMemberData()
    {
        return [
            'source_url' => 'http://booklet.dev/viral',
            'target_url' => 'http://booklet.dev/viral-dziekujemy',
            'email' => 'adam@test.com',
            'name' => 'Adam',
            'status' => 'confirmed',
            'points' => 0,
            'reference_coupon' => 'xyz123_recommendation',
        ];
    }
}
