<?php


namespace Poseidonphp\LaravelOidcClient;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Poseidonphp\LaravelOidcClient\Exceptions\OpenIDConnectClientException;


class OidcClient
{

    public function __construct()
    {
        /**
         * Require the CURL and JSON PHP extensions to be installed
         */
        if (!function_exists('curl_init')) {
            throw new OpenIDConnectClientException('OpenIDConnect needs the CURL PHP extension.');
        }
        if (!function_exists('json_decode')) {
            throw new OpenIDConnectClientException('OpenIDConnect needs the JSON PHP extension.');
        }
    }

    public function decom_pingLogin(Request $request)
    {
        $userModel = new (config('oidc.user_model'));

        $oidc = new OpenIDConnect(
            config('oidc.ping_federate_url'),
            config('oidc.ping_federate_client_id'),
            config('oidc.ping_federate_secret')
        );
        $oidc->setRedirectURL(config('ping_federate_redirect_uri'));
        $oidc->addScope(['openid', 'profile']);
        $oidc->setVerifyHost(false);
        $oidc->setVerifyPeer(false);

        if($request->has('intended') && $request->input('intended') !== null) {
            $oidc->setIntendedUrl($request->input('intended'));
        }

        try {
            $oidc->authenticate();
        } catch (OpenIDConnectClientException $exception) {
            abort(400, 'Something went wrong with the OIDC client or IDP');
        }
        $email = $oidc->getVerifiedClaims('mail');
        $username = $oidc->getVerifiedClaims('uid');

        // 1. Get user in database
        // 2a. Create user if not exist - OR -
        // 2b. Update user from LDAP - or from claim?
        // 3. Log in

        // 1 - look for user in database
        try {
            $user = $userModel::where('username', '=', $username)->firstOrFail();
            // 2b - update user
        } catch (ModelNotFoundException $exception) {
            // 2a - Create user
            $user = new (config('oidc.user_model'));
            $user->name = $oidc->getVerifiedClaims('name');
            $user->username = $username;
            $user->password = Hash::make(Str::random());
            $user->email = $email;
            $user->employee_id = $oidc->getVerifiedClaims('employeeNumber');
            $user->save();
        }
        // 3 - Log in
        config(['session.lifetime' => 5]);
        Auth::login($user);
        $request->session()->regenerate();

        if($oidc->intendedUrl) {
            return response()->redirectTo($oidc->intendedUrl);
        }

        return redirect('/');
    }
}
