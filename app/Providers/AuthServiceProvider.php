<?php

namespace App\Providers;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\ItemRequest;
use App\Models\License;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\StatusLabel;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\AccessoryPolicy;
use App\Policies\AssetPolicy;
use App\Policies\AssetModelPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ComponentPolicy;
use App\Policies\ConsumablePolicy;
use App\Policies\ItemRequestPolicy;
use App\Policies\LicensePolicy;
use App\Policies\LocationPolicy;
use App\Policies\ManufacturerPolicy;
use App\Policies\StatusLabelPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Asset::class, AssetPolicy::class);
        Gate::policy(License::class, LicensePolicy::class);
        Gate::policy(Accessory::class, AccessoryPolicy::class);
        Gate::policy(Consumable::class, ConsumablePolicy::class);
        Gate::policy(Component::class, ComponentPolicy::class);
        Gate::policy(ItemRequest::class, ItemRequestPolicy::class);
        Gate::policy(AssetModel::class, AssetModelPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(Manufacturer::class, ManufacturerPolicy::class);
        Gate::policy(StatusLabel::class, StatusLabelPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
