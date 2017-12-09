<?php
class ViralWidgetRequest
{
    public function getMemberData($recommendation_code_value)
    {
        if (ViralWidget::isTestMode()) {
            return MemberDataFactory::testMemberData();
        }

        try {
            $data = @file_get_contents($this->memberDataUrl($recommendation_code_value));
            if ($data !== false) {
                $data = json_decode($data);
                return (array) $data->data[0]->attributes;
            }
        } catch (Throwable $t) {}

        return null;
    }

    private function memberDataUrl($recommendation_code_value)
    {
        return ViralWidgetApi::getApiUrl() . 'viral_member_data/' . $recommendation_code_value;
    }
}
