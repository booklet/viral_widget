<?php
class ViralWidgetUrlTest extends TesterCase
{
    public function testRemoveRecommendationCodeFormUrlParamsMultipleParams()
    {
        $test_url = 'http://fotobum.pl/viral?recommendation_code=22ehdb&param2=val2&param3=val3';
        $url_witout_recommendation_code = $this->url()->createCurrentUrlWitoutRecommendationCodeParam($test_url);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral?param2=val2&param3=val3');
    }

    public function testRemoveRecommendationCodeFormUrlParamsSingleParam()
    {
        $test_url = 'http://fotobum.pl/viral?recommendation_code=22ehdb';
        $url_witout_recommendation_code = $this->url()->createCurrentUrlWitoutRecommendationCodeParam($test_url);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral');
    }

    public function testCreateCurrentUrlWithoutRecommendationCodeInMultipleParams()
    {
        $test_url = 'http://fotobum.pl/viral?param2=val2&param3=val3';
        $url_witout_recommendation_code = $this->url()->createCurrentUrlWitoutRecommendationCodeParam($test_url);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral?param2=val2&param3=val3');
    }

    public function testCreateCurrentUrlWithoutRecommendationCodeInNonParamUrl()
    {
        $test_url = 'http://fotobum.pl/viral';
        $url_witout_recommendation_code = $this->url()->createCurrentUrlWitoutRecommendationCodeParam($test_url);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral');

    }

    public function testCreateCurrentUrlWithoutRecommendationWhenRecommendationCodeAsPartOfUrl()
    {
        $test_url = 'http://fotobum.pl/viral-thansk/abc123';
        $url_witout_recommendation_code = $this->url()->createCurrentUrlWitoutRecommendationCodeParam($test_url, ['recommendation_code' => 'abc123']);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral-thansk');
    }



    public function testCreateCurrentUrlWithoutRecommendationWhenRecomendationInParamsAndAsPartOfUrl()
    {
        $test_url = 'http://fotobum.pl/viral?recommendation_code=22ehdb&param2=val2&param3=val3';
        $url_witout_recommendation_code = $this->url()->createCurrentUrlWitoutRecommendationCodeParam($test_url, ['recommendation_code' => '22ehdb']);

        Assert::expect($url_witout_recommendation_code)->to_equal('http://fotobum.pl/viral?param2=val2&param3=val3');
    }




    private function url()
    {
        return new ViralWidgetUrl();
    }
}
