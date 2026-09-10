<?php

use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(["auth", "verified"])->prefix("admin")->as("admin.")->group(function () {
    Route::middleware("can:manage-master-data")->group(function () {
        Route::controller(MasterDataController::class)->group(function () {
            Route::get("suppliers", "suppliers")->name("suppliers.index");
            Route::get("suppliers/create", "createSupplier")->name("suppliers.create");
            Route::post("suppliers", "storeSupplier")->name("suppliers.store");
            Route::get("suppliers/{supplier}/edit", "editSupplier")->name("suppliers.edit");
            Route::put("suppliers/{supplier}", "updateSupplier")->name("suppliers.update");
            Route::delete("suppliers/{supplier}", "destroySupplier")->name("suppliers.destroy");
            Route::get("items", "items")->name("items.index");
            Route::get("items/create", "createItem")->name("items.create");
            Route::post("items", "storeItem")->name("items.store");
            Route::get("items/{item}/edit", "editItem")->name("items.edit");
            Route::put("items/{item}", "updateItem")->name("items.update");
            Route::delete("items/{item}", "destroyItem")->name("items.destroy");
            Route::get("customers", "customers")->name("customers.index");
            Route::get("customers/create", "createCustomer")->name("customers.create");
            Route::post("customers", "storeCustomer")->name("customers.store");
            Route::get("customers/{customer}/edit", "editCustomer")->name("customers.edit");
            Route::put("customers/{customer}", "updateCustomer")->name("customers.update");
            Route::delete("customers/{customer}", "destroyCustomer")->name("customers.destroy");
        });
    });
    Route::middleware("can:manage-users")->controller(UserManagementController::class)->group(function () {
        Route::get("users", "index")->name("users.index");
        Route::get("users/create", "create")->name("users.create");
        Route::post("users", "store")->name("users.store");
        Route::get("users/{user}/edit", "edit")->name("users.edit");
        Route::put("users/{user}", "update")->name("users.update");
        Route::delete("users/{user}", "destroy")->name("users.destroy");
        Route::get("roles", "roles")->name("roles.index");
        Route::put("roles/{role}", "updateRole")->name("roles.update");
    });
});
