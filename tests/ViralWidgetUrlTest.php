<?php
class ViralWidgetUrlTest extends TesterCase
{
    public function testIsCurrentUrlContainsRecommendationCodeParam()
    {

    }

    public function testCreateCurrentUrlWitoutRecommendationCodeParam()
    {
        $test_url = 'http://fotobum.pl/viral?recommendation_code=22ehdb&param2=val2&param3=val3';
        $url = new ViralWidgetUrl();
        $url_witout_recommendation_code = $url->createCurrentUrlWitoutRecommendationCodeParam($test_url);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral?param2=val2&param3=val3');

        $test_url = 'http://fotobum.pl/viral?recommendation_code=22ehdb';
        $url_witout_recommendation_code = $url->createCurrentUrlWitoutRecommendationCodeParam($test_url);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral');

        $test_url = 'http://fotobum.pl/viral?param2=val2&param3=val3';
        $url_witout_recommendation_code = $url->createCurrentUrlWitoutRecommendationCodeParam($test_url);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral?param2=val2&param3=val3');

        $test_url = 'http://fotobum.pl/viral';
        $url_witout_recommendation_code = $url->createCurrentUrlWitoutRecommendationCodeParam($test_url);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral');

        $test_url = 'http://fotobum.pl/viral-thansk/abc123';
        $url_witout_recommendation_code = $url->createCurrentUrlWitoutRecommendationCodeParam($test_url, ['recommendation_code' => 'abc123']);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral-thansk');
    }
}
