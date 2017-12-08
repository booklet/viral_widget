<?php
class ViralFormRequest
{
    public function getMemberData()
    {
        if (ViralForm::isTestMode()) {
            return MemberDataFactory::testMemberData();
        }

        try {
            $data = @file_get_contents($this->memberDataUrl());
            if ($data !== false) {
                $data = json_decode($data);
                return (array) $data->data[0]->attributes;
            }
        } catch (Throwable $t) {}

        return null;
    }

    private function memberDataUrl()
    {
        return ViralFormApi::getApiUrl() . 'viral_member_data/' . $this->recommendation_code;
    }
}
