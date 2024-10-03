<?php

namespace Alireza10up\WordpressPlus\Routing;

use Alireza10up\WordpressPlus\Routing\Exceptions\HandlerNotExistException;

class Router
{
    protected static array $post_types = [];

    /**
     * Handle post type and associate it with a controller.
     *
     * @param string $postTypeName
     * @param string $controller
     * @return void
     * @throws HandlerNotExistException
     */
    public static function handlePostTypeWithController(string $postTypeName, string $controller): void
    {
        self::$post_types[$postTypeName] = $controller;

        // TODO Register the custom post type without showing in the default menu
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

        // TODO Register custom admin menu items
        add_action('admin_menu', function() use ($postTypeName, $controller) {
            // TODO List page for the custom post type
            add_menu_page(
                ucfirst($postTypeName) . 's',
                ucfirst($postTypeName) . 's',
                'manage_options',
                "{$postTypeName}_list",
                function() use ($controller) {
                    self::dispatch('index', $controller);
                },
                'dashicons-admin-post', // TODO Custom icon for the menu
                20
            );

            // Add new item
            add_submenu_page(
                "{$postTypeName}_list",
                "Add New " . ucfirst($postTypeName),
                "Add New",
                'manage_options',
                "{$postTypeName}_create",
                function() use ($controller) {
                    self::dispatch('create', $controller);
                }
            );

            // Edit item
            add_submenu_page(
                null,
                "Edit " . ucfirst($postTypeName),
                "Edit",
                'manage_options',
                "{$postTypeName}_edit",
                function() use ($controller) {
                    self::dispatch('edit', $controller);
                }
            );

            // Delete item
            add_submenu_page(
                null,
                "Delete " . ucfirst($postTypeName),
                "Delete",
                'manage_options',
                "{$postTypeName}_delete",
                function() use ($controller) {
                    self::dispatch('delete', $controller);
                }
            );
        });

        // Handle saving and deleting actions
        add_action('admin_post_save_' . $postTypeName, function() use ($controller) {
            $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

            if ($post_id) {
                // If post ID exists, we're updating an existing post
                self::dispatch('update', $controller);
            } else {
                // Otherwise, we're creating a new post
                self::dispatch('store', $controller);
            }
        });

        add_action('admin_post_delete_' . $postTypeName, function() use ($controller) {
            self::dispatch('delete', $controller);
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
