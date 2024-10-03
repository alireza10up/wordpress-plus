<?php

namespace Alireza10up\WordpressPlus\Routing;

use Alireza10up\WordpressPlus\Routing\Exceptions\HandlerNotExistException;

class Router
{
    protected static $post_types = [];

    /**
     * get post type name and controller it auto handle post type handlers
     *
     * @param $postTypeName
     * @param $controller
     * @return void
     * @throws HandlerNotExistException
     */
    public static function handlePostTypeWithController($postTypeName, $controller): void
    {
        self::$post_types[$postTypeName] = $controller;

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

            $post_type_args = [
                'labels' => $labels,
                'public' => true,
                'has_archive' => true,
                'supports' => ['title', 'editor', 'custom-fields'],
                'show_in_menu' => true,
            ];

            register_post_type($postTypeName, $post_type_args);
        });

        add_action('admin_menu', function() use ($postTypeName, $controller) {
            add_submenu_page(
                "edit.php?post_type=$postTypeName",
                "Manage $postTypeName",
                "Manage $postTypeName",
                'manage_options',
                "{$postTypeName}_list",
                function() use ($controller) {
                    self::dispatch('index', $controller);
                }
            );
            add_submenu_page(
                null,
                "Add $postTypeName",
                "Add $postTypeName",
                'manage_options',
                "{$postTypeName}_create",
                function() use ($controller) {
                    self::dispatch('create', $controller);
                }
            );
            add_submenu_page(
                null,
                "Edit $postTypeName",
                "Edit $postTypeName",
                'manage_options',
                "{$postTypeName}_edit",
                function() use ($controller) {
                    self::dispatch('edit', $controller);
                }
            );
        });

        add_action('admin_post_save_' . $postTypeName, function() use ($controller) {
            self::dispatch('store', $controller);
        });

        add_action('admin_post_delete_' . $postTypeName, function() use ($controller) {
            self::dispatch('delete', $controller);
        });
    }

    public static function dispatch($action, $controller): void
    {
        $controllerInstance = new $controller();
        if (method_exists($controllerInstance, $action)) {
            call_user_func([$controllerInstance, $action]);
        } else {
            throw new HandlerNotExistException("Method $action not found in controller!");
        }
    }
}
