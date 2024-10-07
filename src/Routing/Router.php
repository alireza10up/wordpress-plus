<?php

namespace Alireza10up\WordpressPlus\Routing;

use Alireza10up\WordpressPlus\Routing\Exceptions\HandlerNotExistException;

class Router
{
    protected static array $routes = [];

    /**
     * Initialize the Router by setting up necessary hooks.
     *
     * @return void
     */
    public static function init(): void
    {
        add_filter('query_vars', [self::class, 'addQueryVars']);
        add_action('template_redirect', [self::class, 'handleTemplateRedirect']);
        add_action('init', [self::class, 'registerRewriteRules']);
    }

    /**
     * Handle post type and associate it with a controller.
     *
     * @param string $postTypeName
     * @param string $controller
     * @return void
     * @throws HandlerNotExistException
     */
    public static function postType(string $postTypeName, string $controller): void
    {
        self::$routes['postType'][$postTypeName] = $controller;

        self::registerCustomPostType($postTypeName);

        self::registerCustomMenu($postTypeName , $controller);

        self::registerHookAdminForActions($postTypeName, $controller);
    }

    /**
     * Add CRUD for resource in admin WordPress.
     *
     * @param string $resourceName
     * @param string $controller
     * @return void
     */
    public static function resourceAdmin(string $resourceName, string $controller): void
    {
        self::$routes['resourceAdmin'][$resourceName] = $controller;

        self::registerCustomMenu($resourceName, $controller);

        self::registerHookAdminForActions($resourceName, $controller);
    }

    /**
     * Register a route with a specific HTTP method.
     *
     * @param string $method HTTP method (e.g., 'get', 'post').
     * @param string $route Route pattern (e.g., 'product/{id}').
     * @param string $controller Controller class to handle the request.
     * @param string $action Method in the controller to be executed.
     * @return void
     */
    public static function addRoute(string $method, string $route, string $controller, string $action): void
    {
        self::$routes[strtolower($method)][$route] = [$controller, $action];
    }

    /**
     * Magic method to handle dynamic calls for HTTP verbs (e.g., get, post).
     *
     * @param string $name Method name.
     * @param array $arguments Method arguments.
     * @return void
     */
    public static function __callStatic(string $name, array $arguments): void
    {
        $allowedMethods = ['get', 'post', 'put', 'delete', 'patch'];
        if (in_array(strtolower($name), $allowedMethods)) {
            list($route, $controller, $action) = $arguments;
            self::addRoute($name, $route, $controller, $action);
        } else {
            throw new \BadMethodCallException("Method $name is not supported.");
        }
    }

    /**
     * Add a custom query variable to WordPress.
     *
     * @param array $vars Existing query vars.
     * @return array Updated query vars.
     */
    public static function addQueryVars(array $vars): array
    {
        $vars[] = 'wordpress_plus_route';
        return $vars;
    }

    /**
     * Handle template redirect and dispatch to the appropriate controller.
     *
     * @return void
     */
    public static function handleTemplateRedirect(): void
    {
        global $wp;
        $current_route = add_query_arg([], $wp->request);

        if (!empty($current_route)) {
            foreach (['get', 'post'] as $method) {
                if (isset(self::$routes[$method])) {
                    foreach (self::$routes[$method] as $route => $handler) {
                        $regex = '@^' . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $route) . '$@';
                        if (preg_match($regex, $current_route, $matches)) {
                            array_shift($matches);
                            list($controller, $method) = $handler;

                            $controllerInstance = new $controller();
                            if (method_exists($controllerInstance, $method)) {
                                call_user_func_array([$controllerInstance, $method], $matches);
                            } else {
                                throw new HandlerNotExistException('Method ' . $method . ' not found in controller ' . $controller);
                            }
                            exit;
                        }
                    }
                }
            }
        }
    }

    /**
     * Register rewrite rules for all routes.
     *
     * @return void
     */
    public static function registerRewriteRules(): void
    {
        foreach (self::$routes as $methodRoutes) {
            foreach ($methodRoutes as $route => $handler) {
                $regex = self::convertRouteToRegex($route);
                $query = 'index.php?wordpress_plus_route=' . urlencode($route);
                add_rewrite_rule($regex, $query, 'top');
            }
        }

        // Generate hash of current routes
        $currentRoutesHash = md5(json_encode(self::$routes));
        $savedRoutesHash = get_option('wordpress_plus_routes_hash', '');

        // Compare the hashes to determine if routes have changed
        if ($currentRoutesHash !== $savedRoutesHash) {
            flush_rewrite_rules(false);
            update_option('wordpress_plus_routes_hash', $currentRoutesHash);
        }
    }

    /**
     * Register custom menu.
     *
     * @param string $entitieName
     * @param string $controller
     * @return void
     */
    private static function registerCustomMenu(string $entitieName, string $controller): void
    {
        add_action('admin_menu', function() use ($entitieName, $controller) {
            // List page for the custom post type
            add_menu_page(
                ucfirst($entitieName) . 's',
                ucfirst($entitieName) . 's',
                'manage_options',
                "{$entitieName}_list",
                function() use ($controller) {
                    self::dispatch('index', $controller);
                },
                'dashicons-admin-post',
                20
            );

            // Add new item
            add_submenu_page(
                "{$entitieName}_list",
                "Add New " . ucfirst($entitieName),
                "Add New",
                'manage_options',
                "{$entitieName}_create",
                function() use ($controller) {
                    self::dispatch('create', $controller);
                }
            );

            // Edit item
            add_submenu_page(
                null,
                "Edit " . ucfirst($entitieName),
                "Edit",
                'manage_options',
                "{$entitieName}_edit",
                function() use ($controller) {
                    self::dispatch('edit', $controller);
                }
            );
        });
    }

    /**
     * Register the custom post type without showing in the default menu.
     *
     * @param string $postTypeName
     */
    private static function registerCustomPostType(string $postTypeName): void
    {
        add_action('init', function() use ($postTypeName) {
            $labels = [
                'name' => ucfirst($postTypeName) . 's',
                'singular_name' => ucfirst($postTypeName),
                'menu_name' => ucfirst($postTypeName) . 's',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New ' . ucfirst($postTypeName),
                'edit_item' => 'Edit ' . ucfirst($postTypeName),
                'new_item' => 'New ' . ucfirst($postTypeName),
                'view_item' => 'View ' . ucfirst($postTypeName),
                'all_items' => 'All ' . ucfirst($postTypeName) . 's',
            ];

            $postTypeArgs = [
                'labels' => $labels,
                'public' => true,
                'has_archive' => true,
                'supports' => ['title', 'editor', 'custom-fields'],
                'show_ui' => true,           // Show the UI but hide from default menus
                'show_in_menu' => false,     // Hide default menu
            ];

            register_post_type($postTypeName, $postTypeArgs);
        });
    }

    /**
     * Handle saving and deleting actions.
     *
     * @param string $entitieName
     * @param string $controller
     * @return void
     */
    private static function registerHookAdminForActions(string $entitieName, string $controller): void
    {
        add_action('admin_post_save_' . $entitieName, function() use ($controller) {
            self::dispatch('store', $controller);
        });

        add_action('admin_post_delete_' . $entitieName, function() use ($controller) {
            self::dispatch('delete', $controller);
        });

        add_action('admin_post_update_' . $entitieName, function() use ($controller) {
            self::dispatch('update', $controller);
        });
    }

    /**
     * Dispatch action to the appropriate controller method.
     *
     * @param string $action
     * @param string $controller
     * @return void
     * @throws HandlerNotExistException
     */
    public static function dispatch(string $action, string $controller): void
    {
        $controllerInstance = new $controller();
        if (method_exists($controllerInstance, $action)) {
            call_user_func([$controllerInstance, $action]);
        } else {
            throw new HandlerNotExistException("Method $action not found in controller!");
        }
    }

    /**
     * Convert a route pattern into a regex for WordPress rewrite rules.
     *
     * @param string $route Route pattern with placeholders (e.g., 'product/{id}').
     * @return string Regex pattern for WordPress rewrite.
     */
    private static function convertRouteToRegex(string $route): string
    {
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $route);
        return '^' . $pattern . '/?$';
    }
}