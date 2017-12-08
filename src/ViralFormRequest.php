<?php
class ViralFormRequest
{
    public function getMemberData($recommendation_code)
    {
        if (ViralForm::isTestMode()) {
            return MemberDataFactory::testMemberData();
        }

        try {
            $data = @file_get_contents($this->memberDataUrl($recommendation_code));
            if ($data !== false) {
                $data = json_decode($data);
                return (array) $data->data[0]->attributes;
            }
        } catch (Throwable $t) {}

        return null;
    }

    private function memberDataUrl($recommendation_code)
    {
        return ViralFormApi::getApiUrl() . 'viral_member_data/' . $recommendation_code;
    }
}
