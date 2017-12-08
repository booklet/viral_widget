<?php
class ViralFormTest extends TesterCase
{
    public function testGetForm()
    {
        $match = [];
        $get = [];
        $cookie = [];

        $viral = new ViralForm([
            'viral_campaign_hash_id' => 'c51a44ce318175d3c68214f6d5111111',
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);
        $html = $viral->form();

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/c51a44ce318175d3c68214f6d5111111" method="post">');
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[ref_id]" value="">');
        Assert::expect($html)->to_include_string('<input type="email" name="member[email]" class="form-control" id="viral-member-email">');
        Assert::expect($html)->to_include_string('<input type="text" name="member[name]" class="form-control" id="viral-member-name">');
        Assert::expect($html)->to_include_string('<button type="submit" class="btn btn-primary">Zapisz się</button>');
    }

    public function testGetFormWithRefId()
    {
        $match['ref_id'] = 'abc123_registration';
        $get = [];
        $cookie = [];

        $viral = new ViralForm([
            'viral_campaign_hash_id' => 'c51a44ce318175d3c68214f6d5111111',
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);
        $html = $viral->form();

        Assert::expect($html)->to_include_string('<form id="viral-form" action="http://api.booklet.dev/v1/viral_signing_up/c51a44ce318175d3c68214f6d5111111" method="post">');
        Assert::expect($html)->to_include_string('<input type="hidden" name="member[ref_id]" value="abc123_registration">');
        Assert::expect($html)->to_include_string('<input type="email" name="member[email]" class="form-control" id="viral-member-email">');
        Assert::expect($html)->to_include_string('<input type="text" name="member[name]" class="form-control" id="viral-member-name">');
        Assert::expect($html)->to_include_string('<button type="submit" class="btn btn-primary">Zapisz się</button>');
    }


    public function testGetFormWhenUserRegistredByGetParam()
    {
        $match['ref_id'] = 'abc123_registration';
        $get['ref_coupon'] = 'xyz123_recommendation';
        $cookie = [];

        $viral = new ViralForm([
            'viral_campaign_hash_id' => 'c51a44ce318175d3c68214f6d5111111',
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);
        $html = $viral->form();

        Assert::expect($html)->to_equal('
        <div id="viral-recommendation">
          <div class="lead-text">
            Poleceń do tej pory: <span class="points">0</span>.
          </div>
          <div class="recommendation-link">
            <input type="text" value="http://booklet.dev/viral/xyz123_recommendation">
          </div>
          <div class="recommendation-buttons-text">
            Kliknij, aby udostępnić.
          </div>
          <div class="recommendation-buttons">
            <a href="#" class="facebook">Facebook</a>
          </div>
        </div>'
        );
    }

    public function testGetFormWhenUserRegistredByCookie()
    {
        $match['ref_id'] = 'abc123_registration';
        $get = [];
        $cookie['c51a44_ref_coupon'] = 'xyz123_recommendation';

        $viral = new ViralForm([
            'viral_campaign_hash_id' => 'c51a44ce318175d3c68214f6d5111111',
            'routing_match' => $match,
            'get' => $get,
            'cookie' => $cookie,
        ]);
        $html = $viral->form();

        Assert::expect($html)->to_equal('
        <div id="viral-recommendation">
          <div class="lead-text">
            Poleceń do tej pory: <span class="points">0</span>.
          </div>
          <div class="recommendation-link">
            <input type="text" value="http://booklet.dev/viral/xyz123_recommendation">
          </div>
          <div class="recommendation-buttons-text">
            Kliknij, aby udostępnić.
          </div>
          <div class="recommendation-buttons">
            <a href="#" class="facebook">Facebook</a>
          </div>
        </div>'
        );
    }
}
