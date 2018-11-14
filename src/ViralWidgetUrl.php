<?php
class ViralWidgetUrl
{
    public function createCurrentUrlWitoutRecommendationCodeParam(string $url = null, $params)
    {




        $url = $url ?? $_SERVER['REQUEST_URI'];
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
}
