<?php

class shopPriceRouteHelper {

    protected static $plugin_name = 'price';

    public static function getRouteSettings($route = null, $setting = null) {
        if ($route === null) {
            $route = self::getCurrentRouteHash();
        }
        $routes = wa('shop')->getPlugin(self::$plugin_name)->getSettings('routes');
        if (!empty($routes[$route])) {
            $route_settings = $routes[$route];
        } else {
            $route_settings = array();
        }

        if (!$setting) {
            return $route_settings;
        } elseif (!empty($route_settings[$setting])) {
            return $route_settings[$setting];
        } else {
            return null;
        }
    }

    public static function getRouteHash($storefront = null) {
        if ($storefront) {
            return md5($storefront);
        } else {
            return self::getCurrentRouteHash();
        }
    }

    public static function getCurrentRouteHash() {
        $domain = wa()->getRouting()->getDomain(null, true);
        $route = wa()->getRouting()->getRoute();
        return md5($domain . '/' . $route['url']);
    }

    public static function getRouteHashs() {
        $route_hashs = array();
        $routing = wa()->getRouting();
        $domain_routes = $routing->getByApp('shop');
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $route) {
                $route_url = $domain . '/' . $route['url'];
                $route_hashs[$route_url] = md5($route_url);
            }
        }
        return $route_hashs;
    }

}
