<?php

/**
 * PayPalAccess 固有のクレームオブジェクト.
 */
class PayPalAccessClaims extends OIDConnect_Claims {

    var $user_id;
    var $birthday;
    var $language;
    var $account_creation_date;
    var $account_type;
    var $verified_account;
    var $age_range;
    var $customer_id;
    var $create_date;
    var $update_date;

    /**
     * コンストラクタ.
     *
     * 引数の連想配列で, 本オブジェクトのプロパティを設定可能.
     *
     * @param array $arrClaims クレームの情報を格納した連想配列
     */
    public function __construct($arrClaims = array()) {
        if (is_array($arrClaims)) {
            $this->setPropertiesFromArray($arrClaims);
        }
    }

    /**
     * user_id を返す.
     *
     * OIDConnect_Claims::getSub() のラッパーです.
     * @see http://hg.openid.net/connect/issue/687/all-inconsistency-between-user_id-and-prn
     */
    public function getUserId() {
        if (is_null($this->user_id)) {
            $this->user_id = $this->getSub();
        }
        return $this->user_id;
    }

    /**
     * user_id を設定する.
     * OIDConnect_Claims::setSub() のラッパーです.
     *
     * @see http://hg.openid.net/connect/issue/687/all-inconsistency-between-user_id-and-prn
     */
    public function setUserId($user_id) {
        $this->setSub($user_id);
        $this->user_id = $user_id;
    }

    /**
     * birthday を返す.
     * OIDConnect_Claims::getBirthdate() のラッパーです.
     */
    public function getBirthday() {
        if (is_null($this->birthday)) {
            $this->birthday = $this->getBirthdate();
        }
        return $this->birthday;
    }

    /**
     * birthday を設定する.
     * OIDConnect_Claims::setBirthdate() のラッパーです.
     */
    public function setBirthday($birthday) {
        $this->setBirthdate($birthday);
        $this->birthday = $birthday;
    }

    /**
     * gender を数値に置きかえて返します.
     *
     * @return integer male: 1, female: 2
     */
    public function getGenderAsNumber() {
        switch ($this->getGender()) {
            case 'male':
                return '1';
                break;
            case 'female':
                return '2';
                break;
            default:
        }
        return null;
    }

    public function setLanguage($language) {
        $this->language = $language;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function setAccountCreationDate($account_creation_date) {
        $this->account_creation_date = $account_creation_date;
    }

    public function getAccountCreationDate() {
        return $this->account_creation_date;
    }

    public function setAccountType($account_type) {
        return $this->account_type;
    }

    public function setVerifiedAccount($verified_account) {
        $this->verified_account = $verified_account;
    }

    public function getVerifiedAccount() {
        return $this->verified_account;
    }

    public function isVerifiedAccount() {
        if ($this->getVerifiedAccount()) {
            return true;
        }
        return false;
    }

    public function setAgeRan($age_range) {
        $this->age_range = $age_range;
    }

    public function getAgeRange() {
        return $this->age_range;
    }

    public function setCustomerId($customer_id) {
        $this->customer_id = $customer_id;
    }

    public function getCustomerId() {
        return $this->customer_id;
    }

    public function getCreateDate() {
        return $this->create_date;
    }

    public function getUpdateDate() {
        return $this->update_date;
    }

    /**
     * EC-CUBE 顧客として存在するかどうか.
     *
     * @return boolean EC-CUBEの顧客として存在する場合は true. 存在しない, 又は既に退会済みの場合は false
     */
    public function existsCustomer() {
        if (!SC_Utils_Ex::isBlank($this->getCustomerId())) {
            $arrCustomer = SC_Helper_Customer_Ex::sfGetCustomerDataFromId($this->getCustomerId(), 'del_flg = 0');
            if (!SC_Utils_Ex::isBlank($arrCustomer)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 引数の連想配列を元にプロパティを設定する.
     *
     * @param array プロパティの情報を格納した連想配列
     * @param ReflectionClass $parentClass 親のクラス. 本メソッドの内部的に使用します.
     */
    public function setPropertiesFromArray($arrProps, ReflectionClass $parentClass = null) {
        $objReflect = null;
        if (is_object($parentClass)) {
            $objReflect = $parentClass;
        } else {
            $objReflect = new ReflectionClass($this);
        }
        $arrProperties = $objReflect->getProperties();
        foreach ($arrProperties as $objProperty) {
            // $objProperty->setAccessible(true); XXX PHP5.3.2 未満未対応...
            $name = $objProperty->getName();
            $objProperty->setValue($this, $arrProps[$name]);
        }
        $parentClass = $objReflect->getParentClass();
        if (is_object($parentClass)) {
            self::setPropertiesFromArray($arrProps, $parentClass);
        }
    }

    /**
     * 本オブジェクトの内容を連想配列で返します.
     *
     * @param ReflectionClass $parentClass 親のクラス. 本メソッドの内部的に使用します.
     */
    public function toArray(ReflectionClass $parentClass = null) {
        $objReflect = null;
        if (is_object($parentClass)) {
            $objReflect = $parentClass;
        } else {
            $objReflect = new ReflectionClass($this);
        }
        $arrProperties = $objReflect->getProperties();
        foreach ($arrProperties as $objProperty) {
            // $objProperty->setAccessible(true); XXX PHP5.3.2 未満未対応...
            $name = $objProperty->getName();
            $arrResults[$name] = $objProperty->getValue($this);
        }
        $parentClass = $objReflect->getParentClass();
        if (is_object($parentClass)) {
            $arr = self::toArray($parentClass);
            $arrResults = array_merge(self::toArray($parentClass), $arrResults);
        }

        // type casting.
        $arrResults['verified_account'] = $this->isVerifiedAccount() ? '1' : '0';
        // address は StdClass なので JSON にして返す
        $arrResults['address'] = is_object($arrResults['address']) ? SC_Utils_Ex::jsonEncode($arrResults['address']) : $arrResults['address'];
        return $arrResults;
    }

    /**
     * 引数の Profile オブジェクトから, プロパティをコピーします.
     *
     * @param StdClass $objProfile UserInfo endpoint から取得した Profile オブジェクト
     * @param ReflectionClass $parentClass 親のクラス. 本メソッドの内部的に使用します.
     */
    public function copyFrom($objProfile, ReflectionClass $parentClass = null) {
        $objReflect = null;
        if (is_object($parentClass)) {
            $objReflect = $parentClass;
        } else {
            $objReflect = new ReflectionClass($this);
        }
        $arrProperties = $objReflect->getProperties();
        foreach ($arrProperties as $objProperty) {
            // $objProperty->setAccessible(true); XXX PHP5.3.2 未満未対応...
            $name = $objProperty->getName();
            if (isset($objProfile->$name)) {
                $objProperty->setValue($this, $objProfile->$name);
            }
        }
        $parentClass = $objReflect->getParentClass();
        if (is_object($parentClass)) {
            self::copyFrom($objProfile, $parentClass);
        }
        // copy to wrapper.
        $this->setSub($this->getUserId());
        $this->setBirthdate($this->getBirthday());
    }

    /**
     * EC-CUBE の顧客情報に適合した連想配列を返します.
     *
     * @return array EC-CUBEの顧客情報の連想配列
     */
    public function toCustomerArray() {
        $arrResults = array();
        $arrResults['customer_id'] = $this->getCustomerId();
        $arrResults['name01'] = $this->getFamilyName();
        $arrResults['name02'] = $this->getGivenName();
        $objAddress = $this->getAddressAsObject();
        if (is_object($objAddress)) {
            list($arrResults['zip01'],
                 $arrResults['zip02']) = $objAddress->splitPostalCode();
            $arrResults['pref']= $this->getPrefId($objAddress->getRegion());
            $arrResults['addr01'] = $objAddress->getLocality();
            $arrResults['addr02'] = $objAddress->getStreetAddress();
        }

        $arrResults['email'] = $this->getEmail();
        $arrTel = $this->splitPhoneNumber();
        if (is_array($arrTel)) {
            list($arrResults['tel01'],
                 $arrResults['tel02'],
                 $arrResults['tel03']) = $arrTel;
        }
        $arrResults['birth'] = $this->getBirthday();
        $arrResults['password'] = DEFAULT_PASSWORD;
        $arrResults['reminder_answer'] = DEFAULT_PASSWORD;
        if (is_null($this->getCustomerId())) {
            $arrResults['secret_key'] = SC_Helper_Customer_Ex::sfGetUniqSecretKey();
            if (CUSTOMER_CONFIRM_MAIL == false) {
                $arrResults['status'] = '2'; // 本会員
            } else {
                $arrResults['status'] = '1'; // 仮会員
            }
        }
        $arrResults['del_flg'] = '0';
        return $arrResults;
    }

    /**
     * 都道府県名から都道府県IDを取得する.
     *
     * @param string $pref_name 都道府県名
     * @return integer 都道府県ID
     */
    protected function getPrefId($pref_name) {
        $arrPref = array('Hokkaido' => 1,
                         '北海道' => 1,
                         'Aomori' => 2,
                         '青森県' => 2,
                         'Iwate' => 3,
                         '岩手県' => 3,
                         'Miyagi' => 4,
                         '宮城県' => 4,
                         'Akita' => 5,
                         '秋田県' => 5,
                         'Yamagata' => 6,
                         '山形県' => 6,
                         'Fukushima' => 7,
                         '福島県' => 7,
                         'Ibaraki' => 8,
                         '茨城県' => 8,
                         'Tochigi' => 9,
                         '栃木県' => 9,
                         'Gunma' => 10,
                         '群馬県' => 10,
                         'Saitama' => 11,
                         '埼玉県' => 11,
                         'Chiba' => 12,
                         '千葉県' => 12,
                         'Tokyo' => 13,
                         '東京都' => 13,
                         'Kanagawa' => 14,
                         '神奈川県' => 14,
                         'Niigata' => 15,
                         '新潟県' => 15,
                         'Toyama' => 16,
                         '富山県' => 16,
                         'Ishikawa' => 17,
                         '石川県' => 17,
                         'Fukui' => 18,
                         '福井県' => 18,
                         'Yamanashi' => 19,
                         '山梨県' => 19,
                         'Nagano' => 20,
                         '長野県' => 20,
                         'Gifu' => 21,
                         '岐阜県' => 21,
                         'Shizuoka' => 22,
                         '静岡県' => 22,
                         'Aichi' => 23,
                         '愛知県' => 23,
                         'Mie' => 24,
                         '三重県' => 24,
                         'Shiga' => 25,
                         '滋賀県' => 25,
                         'Kyoto' => 26,
                         '京都府' => 26,
                         'Osaka' => 27,
                         '大阪府' => 27,
                         'Hyogo' => 28,
                         '兵庫県' => 28,
                         'Nara' => 29,
                         '奈良県' => 29,
                         'Wakayama' => 30,
                         '和歌山県' => 30,
                         'Tottori' => 31,
                         '鳥取県' => 31,
                         'Shimane' => 32,
                         '島根県' => 32,
                         'Okayama' => 33,
                         '岡山県' => 33,
                         'Hiroshima' => 34,
                         '広島県' => 34,
                         'Yamaguchi' => 35,
                         '山口県' => 35,
                         'Tokushima' => 36,
                         '徳島県' => 36,
                         'Kagawa' => 37,
                         '香川県' => 37,
                         'Ehime' => 38,
                         '愛媛県' => 38,
                         'Kochi' => 39,
                         '高知県' => 39,
                         'Fukuoka' => 40,
                         '福岡県' => 40,
                         'Saga' => 41,
                         '佐賀県' => 41,
                         'Nagasaki' => 42,
                         '長崎県' => 42,
                         'Kumamoto' => 43,
                         '熊本県' => 43,
                         'Oita' => 44,
                         '大分県' => 44,
                         'Miyazaki' => 45,
                         '宮崎県' => 45,
                         'Kagoshima' => 46,
                         '鹿児島県' => 46,
                         'Okinawa' => 47,
                         '沖縄県' => 47
                         );
        return $arrPref[$pref_name];
    }

}
