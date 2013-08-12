<?php

/**
 * OpenID Connect のクレームオブジェクト
 */
class OIDConnect_Claims {

    var $sub;
    var $name;
    var $family_name;
    var $given_name;
    var $middle_name;
    var $nickname;
    var $preferred_username;
    var $profile;
    var $picture;
    var $website;
    var $gender;
    var $birthdate;
    var $zoneinfo;
    var $locale;
    var $updated_time;
    var $email;
    var $email_verified;
    var $address;
    var $phone_number;

    public function __construct($arrClaims = array()) {
        if (is_array($arrClaims)) {
            $this->setPropertiesFromArray($arrClaims);
        }
    }

    public function getSub() {
        return $this->sub;
    }

    public function setSub($sub) {
        $this->sub = $sub;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setFamilyName($family_name) {
        $this->family_name = $family_name;
    }
    public function getFamilyName() {
        return $this->family_name;
    }

    public function setGivenName($given_name) {
        $this->given_name = $given_name;
    }

    public function getGivenName() {
        return $this->given_name;
    }

    public function setMiddleName($middle_name) {
        $this->middle_name = $middle_name;
    }

    public function getMiddleName() {
        return $this->middle_name;
    }

    public function setNickname($nickname) {
        $this->nickname = $nickname;
    }

    public function getNickname() {
        return $this->nickname;
    }

    public function setPreferredUsername($preferred_username) {
        $this->preferred_username = $preferred_username;
    }

    public function getPreferredUsername() {
        return $this->preferred_username;
    }

    public function setProfile($profile) {
        $this->profile = $profile;
    }

    public function getProfile() {
        return $this->profile;
    }

    public function setPicture($picture) {
        $this->picture = $picture;
    }

    public function getPicture() {
        return $this->picture;
    }

    public function setWebsite($website) {
        $this->website = $website;
    }

    public function getWebsite() {
        return $this->website;
    }

    public function setGender($gender) {
        $this->gender = $gender;
    }

    public function getGender() {
        return $this->gender;
    }

    public function setBirthdate($birthdate) {
        $this->birthdate = $birthdate;
    }

    public function getBirthdate() {
        return $this->birthdate;
    }

    public function setZoneinfo($zoneinfo) {
        $this->zoneinfo = $zoneinfo;
    }

    public function getZoneinfo() {
        return $this->zoneinfo;
    }

    public function setLocale($locale) {
        $this->locale = $locale;
    }

    public function getLocale() {
        return $this->locale;
    }

    public function setUpdatedTime($updated_time) {
        $this->updated_time = $updated_time;
    }

    public function getUpdatedTime() {
        return $this->updated_time;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmailVerified($email_verified) {
        $this->email_verified = $email_verified;
    }

    public function getEmailVerified() {
        return $this->email_verified;
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    public function getAddress() {
        return $this->address;
    }

    public function setPhoneNumber($phone_number) {
        $this->phone_number = $phone_number;
    }

    public function getPhoneNumber() {
        return $this->phone_number;
    }

    /**
     * 電話番号を3分割します.
     */
    public function splitPhoneNumber() {
        if (is_null($this->getPhoneNumber())) {
            return null;
        }
        $tel = preg_replace('/[^0-9]/', '', $this->getPhoneNumber());
        $tel1 = substr($tel, 0, 4);
        $tel2 = substr($tel, 4, 4);
        $tel3 = substr($tel, 8);
        $arrTel = array($tel1 === false ? '' : $tel1,
                        $tel2 === false ? '' : $tel2,
                        $tel3 === false ? '' : $tel3);
        return $arrTel;
    }

    /**
     * Address を StdClass で返します.
     */
    public function getAddressAsObject() {
        if (!is_null($this->getAddress())) {
            $objAddress = new OIDConnect_Claims_Address($this->getAddress());
            return $objAddress;
        }
        return null;
    }

    /**
     * 連想配列からプロパティを設定します.
     */
    public function setPropertiesFromArray($arrProperties) {
        foreach ($arrProperties as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    /**
     * プロパティの値を連想配列で返します.
     */
    public function toArray() {
        $arrResults = array();
        $objReflect = new ReflectionClass($this);
        $arrProperties = $objReflect->getProperties();

        foreach ($arrProperties as $objProperty) {
            // $objProperty->setAccessible(true); XXX PHP5.3.2 未満未対応...
            $name = $objProperty->getName();
            $arrResults[$name] = $objProperty->getValue($this);
        }
        return $arrResults;
    }

    /**
     * StdClass からプロパティの内容をコピーします.
     */
    public function copyFrom($objProfile) {
        $objReflect = new ReflectionClass($this);
        $arrProperties = $objReflect->getProperties();
        foreach ($arrProperties as $objProperty) {
            // $objProperty->setAccessible(true); XXX PHP5.3.2 未満未対応...
            $name = $objProperty->getName();
            $objProperty->setValue($this, $objProfile->$name);
        }
    }
}

/**
 * Address を表すオブジェクト
 */
class OIDConnect_Claims_Address {
    var $formatted;
    var $street_address;
    var $locality;
    var $region;
    var $postal_code;
    var $country;

    public function __construct($objAddress = null) {
        if (is_object($objAddress)) {
            $this->copyFrom($objAddress);
        }
    }

    public function setFormatted($formatted) {
        $this->formatted = $formatted;
    }

    public function getFormatted() {
        return $this->formatted;
    }

    public function setStreetAddress($street_address) {
        $this->street_address = $street_address;
    }

    public function getStreetAddress() {
        return $this->street_address;
    }

    public function setLocality($locality) {
        $this->locality = $locality;
    }

    public function getLocality() {
        return $this->locality;
    }

    public function setRegion($region) {
        $this->region = $region;
    }

    public function getRegion() {
        return $this->region;
    }

    public function setPostalCode($postal_code) {
        $this->postal_code = $postal_code;
    }

    public function getPostalCode() {
        return $this->postal_code;
    }

    public function setCountry($country) {
        $this->country = $country;
    }

    /**
     * 郵便番号を分割する
     */
    public function splitPostalCode() {
        $postal_code = preg_replace('/[^0-9]/', '', $this->getPostalCode());
        $zip1 = substr($postal_code, 0, 3);
        $zip2 = substr($postal_code, 3);
        $arrZip = array($zip1 === false ? '' : $zip1,
                        $zip2 === false ? '' : $zip2);
        return $arrZip;
    }

    /**
     * StdClass のプロパティを, 自分自身にコピーします.
     */
    public function copyFrom($objRawAddress) {
        $objReflect = new ReflectionClass($this);
        $arrProperties = $objReflect->getProperties();
        foreach ($arrProperties as $objProperty) {
            // $objProperty->setAccessible(true); XXX PHP5.3.2 未満未対応...
            $name = $objProperty->getName();
            $objProperty->setValue($this, $objRawAddress->$name);
        }
    }
}