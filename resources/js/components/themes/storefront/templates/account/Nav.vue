<template>
    <div class="list-group account-left-nav-container">
        <router-link to="/account/orders" class="list-group-item list-group-item-action">{{ $t('Orders') }}</router-link>
        <router-link to="/account/profile" class="list-group-item list-group-item-action">{{ $t('Profile') }}</router-link>
        <router-link to="/account/address-book" class="list-group-item list-group-item-action">{{ $t('Address Book') }}</router-link>
        <router-link to="/account/password" class="list-group-item list-group-item-action">{{ $t('Update Password') }}</router-link>

        <!-- Used to hook into the account navigation. -->
        <template v-for="(component, index) in $pluginStorefrontHooks['account_nav']" :key="index">
            <component :is="component"></component>
        </template>
        
        <router-link to="/logout" class="list-group-item list-group-item-action">{{ $t('Logout') }}</router-link>
    </div>
</template>