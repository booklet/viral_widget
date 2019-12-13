<?php
class ViralWidgetUrl
{
    public function createCurrentUrlWitoutRecommendationCodeParam(string $url = null, $in_params = [])
    {
        $url = $url ?? $_SERVER['REQUEST_URI'];

        // for recommendation code passed as part of url (match)
        // https://fotobum.pl/zabawa-polecaj/eavtmu => https://fotobum.pl/zabawa-polecaj
        $recommendation_code = $in_params[ViralWidget::RECOMMENDATION_KEY] ?? null;
        if ($recommendation_code and !$this->urlHaveRecommendationCodeParam($url)) {
            $url = str_replace('/' . $recommendation_code, '' ,$url);

            return $url;
        }

        // for recommendation code passed in get params
        // http://fotobum.pl/viral?recommendation_code=22ehdb =>'http://fotobum.pl/viral
        $parsed_url = parse_url($url);

        if (empty($parsed_url['query'])) {
            return $url;
        }

        list($url_domain, $url_params) = explode("?", $url);
        parse_str($url_params, $params);
        if (isset($params[ViralWidget::RECOMMENDATION_KEY])) {
            unset($params[ViralWidget::RECOMMENDATION_KEY]);
        }
        $params_string = http_build_query($params);

        if (empty($params_string)) {
            return $url_domain;
        }

        return $url_domain . '?' . $params_string;
    }

    private function urlHaveRecommendationCodeParam(string $url)
    {
        return strpos($url, ViralWidget::RECOMMENDATION_KEY) !== false;
    }
}
