<?php

namespace Alireza10up\WordpressPlus\Routing;

use Alireza10up\WordpressPlus\Routing\Exceptions\HandlerNotExistException;

class Router
{
    protected static array $routes = [];

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
     * Register custom menu
     *  
     * @param string $entitieName
     * @param string $controller
     * @return void
     */
    public static function registerCustomMenu(string $entitieName, string $controller): void
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
                'dashicons-admin-post', // TODO Custom icon for the menu we have changable
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

            // Delete item
            add_submenu_page(
                null,
                "Delete " . ucfirst($entitieName),
                "Delete",
                'manage_options',
                "{$entitieName}_delete",
                function() use ($controller) {
                    self::dispatch('delete', $controller);
                }
            );
        });
    }

    /**
     *  Register the custom post type without showing in the default menu
     * 
     * @param string $postTypeName
     */
    public static function registerCustomPostType(string $postTypeName): void {
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

    public static function registerHookAdminForActions(string $entitieName, string $controller)
    {
        // Handle saving and deleting actions
        add_action('admin_post_save_' . $entitieName, function() use ($controller) {          
            self::dispatch('store', $controller);
        });

        add_action('admin_post_delete_' . $entitieName, function() use ($controller) {
            self::dispatch('delete', $controller);
        });

        add_action('admin_post_update_' . $entitieName, function() use ($controller) {
            self::dispatch('update',$controller); 
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
}
