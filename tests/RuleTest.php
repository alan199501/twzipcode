<?php

use Mockery as m;
use Recca0120\Twzipcode\Rule;
use Recca0120\Twzipcode\Address;

class RuleTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_rule()
    {
        $rule = new Rule('10058,臺北市,中正區,八德路１段,全');
        $this->assertSame('10058', $rule->getZipcode());
        $this->assertSame([['', '', '臺北', '市'], ['', '', '中正', '區'], ['', '', '八德', '路'], ['', '', '1', '段']], $rule->address->getTokens());
        $this->assertSame(['全'], $rule->getTokens());

        $rule = new Rule('10079,臺北市,中正區,三元街,單全');
        $this->assertSame('10079', $rule->getZipcode());
        $this->assertSame([['', '', '臺北', '市'], ['', '', '中正', '區'], ['', '', '三元', '街']], $rule->address->getTokens());
        $this->assertSame(['單', '全'], $rule->getTokens());

        $rule = new Rule('10070,臺北市,中正區,三元街,雙  48號以下');
        $this->assertSame('10070', $rule->getZipcode());
        $this->assertSame([['', '', '臺北', '市'], ['', '', '中正', '區'], ['', '', '三元', '街'], ['48', '', '', '號']], $rule->address->getTokens());
        $this->assertSame(['雙', '以下'], $rule->getTokens());

        $rule = new Rule('10068,臺北市,中正區,大埔街,單  15號以上');
        $this->assertSame('10068', $rule->getZipcode());
        $this->assertSame([['', '', '臺北', '市'], ['', '', '中正', '區'], ['', '', '大埔', '街'], ['15', '', '', '號']], $rule->address->getTokens());
        $this->assertSame(['單', '以上'], $rule->getTokens());

        $rule = new Rule('10043,臺北市,中正區,中華路１段,單  25之   3號以下');
        $this->assertSame('10043', $rule->getZipcode());
        $this->assertSame([['', '', '臺北', '市'], ['', '', '中正', '區'], ['', '', '中華', '路'], ['', '', '1', '段'], ['25', '之3', '', '號']], $rule->address->getTokens());
        $this->assertSame(['單', '以下'], $rule->getTokens());

        $rule = new Rule('26142,宜蘭縣,頭城鎮,宜三路１段,單  27號至  47號');
        $this->assertSame('26142', $rule->getZipcode());
        $this->assertSame([['', '', '宜蘭', '縣'], ['', '', '頭城', '鎮'], ['', '', '宜3', '路'], ['', '', '1', '段'], ['27', '', '', '號'], ['47', '', '', '號']], $rule->address->getTokens());
        $this->assertSame(['單', '至'], $rule->getTokens());

        $rule = new Rule('10052,臺北市,中正區,仁愛路１段,連   2之   4號以上');
        $this->assertSame('10052', $rule->getZipcode());
        $this->assertSame([['', '', '臺北', '市'], ['', '', '中正', '區'], ['', '', '仁愛', '路'], ['', '', '1', '段'], ['2', '之4', '', '號']], $rule->address->getTokens());
        $this->assertSame(['以上'], $rule->getTokens());

        $rule = new Rule('10060,臺北市,中正區,杭州南路１段,　  14號含附號');
        $this->assertSame('10060', $rule->getZipcode());
        $this->assertSame([['', '', '臺北', '市'], ['', '', '中正', '區'], ['', '', '杭州南', '路'], ['', '', '1', '段'], ['14', '', '', '號']], $rule->address->getTokens());
        $this->assertSame(['含附號'], $rule->getTokens());

        $rule = new Rule('10371,臺北市,大同區,哈密街,　  47附號全');
        $this->assertSame('10371', $rule->getZipcode());
        $this->assertSame([['', '', '臺北', '市'], ['', '', '大同', '區'], ['', '', '哈密', '街'], ['47', '', '', '號']], $rule->address->getTokens());
        $this->assertSame(['附號全'], $rule->getTokens());

        $rule = new Rule('32083,桃園市,中壢區,普義,連  49號含附號以下');
        $this->assertSame('32083', $rule->getZipcode());
        $this->assertSame([['', '', '桃園', '市'], ['', '', '中壢', '區'], ['', '', '普義', ''], ['49', '', '', '號']], $rule->address->getTokens());
        $this->assertSame(['含附號以下'], $rule->getTokens());

        $rule = new Rule('40763,臺中市,西屯區,西屯路３段西平南巷,　   1之   3號及以上附號');
        $this->assertSame('40763', $rule->getZipcode());
        $this->assertSame([['', '', '臺中', '市'], ['', '', '西屯', '區'], ['', '', '西屯', '路'], ['', '', '3', '段'], ['', '', '西平南', '巷'], ['1', '之3', '', '號']], $rule->address->getTokens());
        $this->assertSame(['及以上附號'], $rule->getTokens());

        $rule = new Rule('23553,新北市,中和區,連城路,雙 268之   1號以下');
        $this->assertSame('23553', $rule->getZipcode());
        $this->assertSame([['', '', '新北', '市'], ['', '', '中和', '區'], ['', '', '連城', '路'], ['268', '之1', '', '號']], $rule->address->getTokens());
        $this->assertSame(['雙', '以下'], $rule->getTokens());

        $rule = new Rule('24341,新北市,泰山區,全興路,全');
        $this->assertSame('24341', $rule->getZipcode());
        $this->assertSame([['', '', '新北', '市'], ['', '', '泰山', '區'], ['', '', '全興', '路']], $rule->address->getTokens());
        $this->assertSame(['全'], $rule->getTokens());
    }

    public function test_match()
    {
        $address = '臺北市大安區市府路5號';

        // 全單雙
        $this->assertTrue((new Rule('臺北市大安區市府路全'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路單全'))->match($address));
        $this->assertFalse((new Rule('臺北市大安區市府路雙全'))->match($address));

        // 以上 & 以下
        $this->assertFalse((new Rule('臺北市大安區市府路6號以上'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路6號以下'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路5號以上'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路5號'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路5號以下'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路4號以上'))->match($address));
        $this->assertFalse((new Rule('臺北市大安區市府路4號以下'))->match($address));

        // 至
        $this->assertFalse((new Rule('臺北市大安區市府路1號至4號'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路1號至5號'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路5號至9號'))->match($address));
        $this->assertFalse((new Rule('臺北市大安區市府路6號至9號'))->match($address));

        // 附號
        $this->assertFalse((new Rule('臺北市大安區市府路6號及以上附號'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路6號含附號以下'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路5號及以上附號'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路5號含附號'))->match($address));
        $this->assertFalse((new Rule('臺北市大安區市府路5附號全'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路5號含附號以下'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路4號及以上附號'))->match($address));
        $this->assertFalse((new Rule('臺北市大安區市府路4號含附號以下'))->match($address));

        // 單雙 x 以上, 至, 以下
        $this->assertTrue((new Rule('臺北市大安區市府路單5號以上'))->match($address));
        $this->assertFalse((new Rule('臺北市大安區市府路雙5號以上'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路單1號至5號'))->match($address));
        $this->assertFalse((new Rule('臺北市大安區市府路雙1號至5號'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路單5號至9號'))->match($address));
        $this->assertFalse((new Rule('臺北市大安區市府路雙5號至9號'))->match($address));
        $this->assertTrue((new Rule('臺北市大安區市府路單5號以下'))->match($address));
        $this->assertFalse((new Rule('臺北市大安區市府路雙5號以下'))->match($address));

         // standard rule w/ gradual addresses
        $rule = new Rule('臺北市中正區丹陽街全');
        $this->assertFalse($rule->match('臺北市'));
        $this->assertFalse($rule->match('臺北市中正區'));
        $this->assertFalse($rule->match('臺北市中正區仁愛路１段'));
        $this->assertFalse($rule->match('臺北市中正區仁愛路１段1號'));

        $rule = new Rule('臺北市,中正區,仁愛路１段,　   1號');
        $this->assertFalse($rule->match('臺北市'));
        $this->assertFalse($rule->match('臺北市中正區'));
        $this->assertFalse($rule->match('臺北市中正區仁愛路１段'));
        $this->assertTrue($rule->match('臺北市中正區仁愛路１段1號'));

        // Be careful of the 全! It will bite you!
        $rule = new Rule('臺北市,中正區,八德路１段,全');
        $this->assertTrue($rule->match('臺北市中正區八德路１段1號'));
        $this->assertTrue($rule->match('臺北市中正區八德路１段9號'));
        $this->assertFalse($rule->match('臺北市中正區八德路２段1號'));
        $this->assertFalse($rule->match('臺北市中正區八德路２段9號'));

        $rule = new Rule('臺北市,中正區,三元街,單全');
        $this->assertTrue($rule->match('臺北市中正區三元街1號'));
        $this->assertFalse($rule->match('臺北市中正區三元街2號'));
        $this->assertFalse($rule->match('臺北市中正區大埔街1號'));

        $rule = new Rule('臺北市,大同區,哈密街,　  45巷全');
        $this->assertTrue($rule->match('臺北市大同區哈密街45巷1號'));
        $this->assertTrue($rule->match('臺北市大同區哈密街45巷9號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街46巷1號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街46巷9號'));

        // The address matched by it must have a even number.
        $rule = new Rule('信義路一段雙全');

        $addr1 = '信義路一段';
        $addr2 = '信義路一段1號';
        $addr3 = '信義路一段2號';

        $this->assertFalse($rule->match($addr1));
        $this->assertFalse($rule->match($addr2));
        $this->assertTrue($rule->match($addr3));

        $rule = new Rule('臺北市,中正區,杭州南路１段,　  14號含附號');
        $this->assertFalse($rule->match('臺北市中正區杭州南路1段13號'));
        $this->assertFalse($rule->match('臺北市中正區杭州南路1段13-1號'));
        $this->assertTrue($rule->match('臺北市中正區杭州南路1段14號'));
        $this->assertTrue($rule->match('臺北市中正區杭州南路1段14-1號'));
        $this->assertFalse($rule->match('臺北市中正區杭州南路1段15號'));
        $this->assertFalse($rule->match('臺北市中正區杭州南路1段15-1號'));

        $rule = new Rule('臺北市,大同區,哈密街,　  47附號全');
        $this->assertFalse($rule->match('臺北市大同區哈密街46號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街46-1號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街47號'));
        $this->assertTrue($rule->match('臺北市大同區哈密街47-1號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街48號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街48-1號'));

        $rule = new Rule('臺北市,大同區,哈密街,雙  68巷至  70號含附號全');
        $this->assertFalse($rule->match('臺北市大同區哈密街66號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街66-1巷'));
        $this->assertFalse($rule->match('臺北市大同區哈密街67號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街67-1巷'));
        $this->assertTrue($rule->match('臺北市大同區哈密街68巷'));
        $this->assertTrue($rule->match('臺北市大同區哈密街68-1號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街69號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街69-1巷'));
        $this->assertTrue($rule->match('臺北市大同區哈密街70號'));
        $this->assertTrue($rule->match('臺北市大同區哈密街70-1號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街71號'));
        $this->assertFalse($rule->match('臺北市大同區哈密街71-1號'));

        $rule = new Rule('桃園縣,中壢市,普義,連  49號含附號以下');
        $this->assertTrue($rule->match('桃園縣中壢市普義48號'));
        $this->assertTrue($rule->match('桃園縣中壢市普義48-1號'));
        $this->assertTrue($rule->match('桃園縣中壢市普義49號'));
        $this->assertTrue($rule->match('桃園縣中壢市普義49-1號'));
        $this->assertFalse($rule->match('桃園縣中壢市普義50號'));
        $this->assertFalse($rule->match('桃園縣中壢市普義50-1號'));

        $rule = new Rule('臺中市,西屯區,西屯路３段西平南巷,　   2之   3號及以上附號');
        $this->assertFalse($rule->match('臺中市西屯區西屯路3段西平南巷1號'));
        $this->assertFalse($rule->match('臺中市西屯區西屯路3段西平南巷1-1號'));
        $this->assertFalse($rule->match('臺中市西屯區西屯路3段西平南巷2號'));
        $this->assertFalse($rule->match('臺中市西屯區西屯路3段西平南巷2-2號'));
        $this->assertTrue($rule->match('臺中市西屯區西屯路3段西平南巷2-3號'));
        $this->assertTrue($rule->match('臺中市西屯區西屯路3段西平南巷3號'));
        $this->assertTrue($rule->match('臺中市西屯區西屯路3段西平南巷3-1號'));
        $this->assertTrue($rule->match('臺中市西屯區西屯路3段西平南巷4號'));
        $this->assertTrue($rule->match('臺中市西屯區西屯路3段西平南巷4-1號'));
    }
}
