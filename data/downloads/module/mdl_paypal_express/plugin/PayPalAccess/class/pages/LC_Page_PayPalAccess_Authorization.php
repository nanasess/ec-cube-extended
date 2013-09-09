<?php
require_once(CLASS_EX_REALDIR . 'page_extends/LC_Page_Ex.php');
require_once(CLASS_EX_REALDIR . 'page_extends/frontparts/LC_Page_FrontParts_LoginCheck_Ex.php');
require_once(CLASS_EX_REALDIR . 'page_extends/entry/LC_Page_Entry_Ex.php');
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/openid_connect/include.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/PayPalAccessClaims.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/PayPalAccessClient.php';
require_once PLUGIN_UPLOAD_REALDIR . 'PayPalAccess/class/helper/SC_Helper_PayPalAccess.php';

/**
 * PayPalAccessの認証クラス
 *
 * @package Page
 * @author Kentaro Ohkouchi
 * @version $Id$
 */
class LC_Page_PayPalAccess_Authorization extends LC_Page_Ex {

    const SESSION_NAME_REFERER = 'plg_paypalaccess_referer_url';
    const SESSION_NAME_ACCESS_TOKEN = 'plg_paypalaccess_access_token';

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        $this->skip_load_page_layout = true;
        parent::init();
        // PayPal認証画面を識別するフラグ
        $this->paypal_access_authorization = true;
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
        parent::process();
        $this->action();
        $this->sendResponse();
    }

    function action() {
        if (!SC_Helper_PayPalAccess::loadConfig()) {
            SC_Response_Ex::actionExit();
        }

        // エラーの場合は, トップページへリダイレクト
        if (isset($_GET['error']) && $_GET['error'] == 'access_denied') {
            $this->tpl_onload = "window.opener.location.href = '" . HTTP_URL. "';window.close();";
            GC_Utils_Ex::gfPrintLog('redirect to ' . HTTP_URL);
            return;
        }

        $arrConfig = SC_Helper_PayPalAccess::getConfig();
        $objClient = PayPalAccessClient::getInstance($arrConfig['app_id'], $arrConfig['app_secret'],
                                                     SC_Helper_PayPalAccess::useSandbox());

        $objClient->setScopesArray(array(PayPalAccessScope::OPENID,
                                         PayPalAccessScope::PROFILE,
                                         PayPalAccessScope::ADDRESS,
                                         PayPalAccessScope::EMAIL,
                                         PayPalAccessScope::PHONE,
                                         PayPalAccessScope::EXTENDED_SCOPE));

        switch ($this->getMode()) {
            // Ajax でアカウントの接続状態を取得する
            case 'ajax_status':
                $objClaims = $this->getCustomerAccounts();
                if (is_object($objClaims)) {
                    $arrStatus = $objClaims->toArray();
                    $arrStatus['requires_revoke'] = $arrConfig['requires_revoke'];
                    $arrStatus['required_check'] = false;
                    // 必須項目のチェック
                    if ($arrStatus['requires_revoke'] == PayPalAccess::REQUIRES_REVOKE_DISABLED) {
                        $objCustomer = new SC_Customer_Ex();
                        if ($objCustomer->isLoginSuccess()) {
                            $kana01 = trim($objCustomer->getValue('kana01'));
                            $kana02 = trim($objCustomer->getValue('kana02'));
                            // フリガナ, 性別が登録されているかチェック
                            if (SC_Utils_Ex::isBlank($kana01)
                                || SC_Utils_Ex::isBlank($kana02)
                                || SC_Utils_Ex::isBlank($objCustomer->getValue('sex'))) {
                                $arrStatus['required_check'] = true;
                                $arrStatus['required_error'] = '<span class="attention">※必須項目が入力されていません。「お名前(フリガナ)」と「性別」は PayPal アカウントから取得できませんので、必ず入力してください。</span>';
                            }
                        }
                    }
                    echo SC_Utils_Ex::jsonEncode($arrStatus);
                    SC_Response_Ex::actionExit();
                }
                echo SC_Utils_Ex::jsonEncode(array('error' => 'Claims is not found.'));
                SC_Response_Ex::actionExit();
                break;

            // Ajax で, アカウントのリンクを解除する
            case 'ajax_unlink':
                $objClaims = $this->getCustomerAccounts();
                if (is_object($objClaims)) {
                    try {
                        // リンク解除して, PayPalアカウントからログアウトする
                        if (SC_Helper_PayPalAccess::unlinkCustomer($objClaims->getUserId())) {
                            $objToken = SC_Helper_PayPalAccess::getToken($objClaims->getUserId());
                            $objClient->endSession($objToken->getIdToken());
                            echo SC_Utils_Ex::jsonEncode(array('success' => 'true'));
                            SC_Response_Ex::actionExit();
                        }
                    } catch (OIDConnect_ClientException $e) {
                        // quiet.
                        GC_Utils_Ex::gfPrintLog(print_r($e, true));
                    }
                }
                echo SC_Utils_Ex::jsonEncode(array('error' => 'unlink error.'));
                SC_Response_Ex::actionExit();
                break;

            // 会員登録を行う
            case 'register':
                if (is_null($_SESSION[self::SESSION_NAME_ACCESS_TOKEN])) {
                    SC_Response_Ex::sendRedirect($_SERVER['PHP_SELF']);
                    SC_Response_Ex::actionExit();
                }
                try {
                    // プロフィールを取得し, クレームオブジェクトへコピーする
                    $objRawProfile = $objClient->getProfile($_SESSION[self::SESSION_NAME_ACCESS_TOKEN]);
                    $objClaims = SC_Helper_PayPalAccess::getClaims($objRawProfile->user_id);
                    unset($_SESSION[self::SESSION_NAME_ACCESS_TOKEN]);
                    $objClaims->copyFrom($objRawProfile);

                    // ログイン中の場合は customer_id を設定する
                    $objCustomer = new SC_Customer_Ex();
                    $customer_email = null;
                    if ($objCustomer->isLoginSuccess()) {
                        // ログイン中だが, クレームに customer_id が含まれなければ, ログアウトして新規登録する
                        if (SC_Utils_Ex::isBlank($objClaims->getCustomerId())) {
                            $objCustomerLogin = new SC_Customer();
                            $objCustomerLogin->EndSession();
                            $customer_email = $objClaims->getEmail();
                        } else {
                            $customer_email = $objCustomer->getValue('email');
                        }
                    }

                    // 会員登録をする
                    $customer_id = SC_Helper_PayPalAccess::registerCustomer($objClaims);
                    $arrCustomer = SC_Helper_Customer_Ex::sfGetCustomerData($customer_id);

                    if (SC_Utils_Ex::isBlank($customer_email)) {
                        // ログイン中でなければメール送信
                        $objEntryPage = new LC_Page_Entry_Ex();
                        $objEntryPage->lfSendMail($arrCustomer['secret_key'], $arrCustomer);
                    }

                    // 仮会員ではない場合はログイン
                    if (CUSTOMER_CONFIRM_MAIL == false) {
                        SC_Helper_PayPalAccess::doLogin($objClaims, $customer_email);
                        $this->gotoBackURL(new SC_Customer_Ex());
                    } else {
                        $this->gotoBackURL();
                    }
                    return;
                } catch (OIDConnect_ClientException $e) {
                    $this->tpl_onload = "window.opener.location.href = '" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "';window.close();";
                    GC_Utils_Ex::gfPrintLog('redirect to ' . $_SERVER['PHP_SELF']);
                    return;
                }
                break;

            // ログインする
            case 'login':
                if (is_null($_SESSION[self::SESSION_NAME_ACCESS_TOKEN])) {
                    SC_Response_Ex::sendRedirect($_SERVER['PHP_SELF']);
                    SC_Response_Ex::actionExit();
                }

                $_POST['url'] = $_SERVER['PHP_SELF'] . '?mode=link';
                $objLoginCheck = new LC_Page_FrontParts_LoginCheck_Ex();
                $objLoginCheck->init();
                $objLoginCheck->process();
                break;

            // PayPal アカウントとリンクする
            case 'link':
                if (is_null($_SESSION[self::SESSION_NAME_ACCESS_TOKEN])) {
                    SC_Response_Ex::sendRedirect($_SERVER['PHP_SELF']);
                    SC_Response_Ex::actionExit();
                    return;
                }

                $objCustomer = new SC_Customer_Ex();
                if ($objCustomer->isLoginSuccess()) {
                    $objRawProfile = $objClient->getProfile($_SESSION[self::SESSION_NAME_ACCESS_TOKEN]);
                    $objClaims = SC_Helper_PayPalAccess::getClaims($objRawProfile->user_id);
                    unset($_SESSION[self::SESSION_NAME_ACCESS_TOKEN]);
                    $objClaims->setCustomerId($objCustomer->getValue('customer_id'));
                    SC_Helper_PayPalAccess::registerCustomer($objClaims);
                    $this->gotoBackURL($objCustomer);
                    return;
                }
                break;
            default:
        }

        // Id Token を取得し, プロフィールを取得する.
        if ($_GET['code']) {
            try {
                if (isset($_GET['state']) && $_GET['state'] != $_SESSION['state']) {
                    throw new Exception('Illegal state: expected=>' . $_SESSION['state'] . ' actual=>' . $_GET['state']);
                }

                $objRawToken = $objClient->getAccessToken($_GET['code']);
                $objToken = OIDConnect_Token::createInstance($objRawToken);
                // IDトークンを更新
                SC_Helper_PayPalAccess::registerToken($objToken);
                // UserInfo 取得
                $objRawProfile = $objClient->getProfile($objToken->getAccessToken());
                // 顧客の存在チェック
                $objClaims = SC_Helper_PayPalAccess::getClaims($objToken->getUserId());
                $objClaims->copyFrom($objRawProfile);
                if ($objClaims->existsCustomer()) {
                    // 存在する場合はプロフィール更新し, ログイン状態に
                    $objCustomer = new SC_Customer_Ex();
                    $customer_email = null;
                    if ($objCustomer->isLoginSuccess()) {
                        /*
                         * ログイン中の customer_id と クレームの customer_id が異なる場合は一旦ログアウト
                         * クレームのアカウントでログインし直す
                         */
                        if ($objCustomer->getValue('customer_id') != $objClaims->getCustomerId()) {
                            $objCustomerLogin = new SC_Customer();
                            $objCustomerLogin->EndSession();
                            $customer_email = $objClaims->getEmail();
                        } else {
                            $customer_email = $objCustomer->getValue('email');
                        }
                    } else {
                        $arrCustomer = SC_Helper_Customer_Ex::sfGetCustomerDataFromId($objClaims->getCustomerId(), 'del_flg = 0');
                        $customer_email = $arrCustomer['email'];
                    }
                    SC_Helper_PayPalAccess::registerCustomer($objClaims);
                    SC_Helper_PayPalAccess::doLogin(null, $customer_email);
                    $this->gotoBackURL($objCustomer);
                    return;
                } else {
                    /*
                     * TODO 存在しない場合は, クレームを保存し, ログイン画面を表示
                     * ここでログインすると, PayPalアカウントと関連づけされる.
                     */
                    SC_Helper_PayPalAccess::registerClaims($objClaims);
                    $_SESSION[self::SESSION_NAME_ACCESS_TOKEN] = $objToken->getAccessToken();
                    $objCustomer = new SC_Customer_Ex();
                    // ログインしていたら register へリダイレクト
                    if ($objCustomer->isLoginSuccess()) {
                        SC_Response_Ex::sendRedirect($_SERVER['PHP_SELF'], array('mode' => 'register'));
                        SC_Response_Ex::actionExit();
                    }
                    $this->tpl_title = 'PayPalアカウントでログイン';
                    $this->setTemplate(PLUGIN_UPLOAD_REALDIR . '/PayPalAccess/templates/entry.tpl');
                }
            } catch (OIDConnect_ClientException $e) {
                switch ($e->getCode()) {
                    // ErrorCode 400,401 の場合は認証エラーなので,再認証
                    case OIDConnect_ClientException::AUTH_ERROR_CODE:
                        $auth_url = $objClient->getAuthUrl();
                        $auth_url = SC_Utils_Ex::isBlank($auth_url) ? HTTPS_URL : $auth_url;
                        GC_Utils_Ex::gfPrintLog(print_r($e, true));
                        GC_Utils_Ex::gfPrintLog('Error! redirect to ' . $auth_url);
                        header('Location: ' . $auth_url);
                        break;
                    case OIDConnect_ClientException::BAD_REQUEST_ERROR_CODE:
                        GC_Utils_Ex::gfPrintLog(print_r($e, true));
                        $this->tpl_onload = "window.opener.location.href = '" . HTTP_URL. "';window.close();";
                        GC_Utils_Ex::gfPrintLog('redirect to ' . HTTP_URL);
                        return;
                    default:
                }
                throw new Exception($e);
            }
            /*
            if (SC_Utils_Ex::isBlank($this->tpl_mainpage)) {
                GC_Utils_Ex::gfPrintLog('tpl_mainpage is empty.');
                SC_Response_Ex::actionExit();
            }
            */
        }
        // Authorization フローを実行する
        else {
            $_SESSION[self::SESSION_NAME_REFERER] = $_SERVER['HTTP_REFERER'];
            $_SESSION['state'] = sha1(time().rand());
            $objClient->setState($_SESSION['state']);
            $url = $objClient->getAuthUrl();
            header('Location: ' . $url);
            SC_Response_Ex::actionExit();
        }
    }

    /**
     * ログイン後のページへ遷移する.
     */
    protected function gotoBackURL(SC_Customer $objCustomer = null) {
        if (is_object($objCustomer)) {
            $arrConfig = SC_Helper_PayPalAccess::getConfig();
            // 必須入力の項目が未入力の場合は Myページへ遷移
            if ($arrConfig['requires_revoke'] == '2') {
                $kana01 = trim($objCustomer->getValue('kana01'));
                $kana02 = trim($objCustomer->getValue('kana02'));
                if (SC_Utils_Ex::isBlank($kana01)
                    || SC_Utils_Ex::isBlank($kana02)
                    || SC_Utils_Ex::isBlank($objCustomer->getValue('sex'))) {
                    $referer_url = '/mypage/change.php';
                    $this->tpl_onload = "window.opener.location.href = '$referer_url';window.close();";
                    GC_Utils_Ex::gfPrintLog('redirect to ' . $referer_url);
                    return;
                }
            }
        }

        if (isset($_SESSION[self::SESSION_NAME_REFERER])) {
            $referer_url = $_SESSION[self::SESSION_NAME_REFERER];
            unset($_SESSION[self::SESSION_NAME_REFERER]);
        } else {
            $referer_url = HTTPS_URL;
        }
        GC_Utils_Ex::gfPrintLog('redirect to ' . $referer_url);
        $this->tpl_onload = "window.opener.location.href = '$referer_url';window.close();";
    }

    /**
     * 登録中の PayPal アカウントを取得する.
     *
     * @return PayPalAccessClaims 登録中のクレーム情報
     */
    protected function getCustomerAccounts() {
        $objCustomer = new SC_Customer_Ex();
        if ($objCustomer->isLoginSuccess()) {
            $customer_id = $objCustomer->getValue('customer_id');
            $objClaims = SC_Helper_PayPalAccess::getClaimsByCustomer($customer_id);
            if (is_object($objClaims)) {
                return $objClaims;
            }
        }
        return null;
    }
}
