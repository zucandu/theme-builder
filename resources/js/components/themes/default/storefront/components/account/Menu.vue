<template>
    <ul class="lg:border lg:mt-0 mt-5 border-gray-300 rounded flex lg:block overflow-x-auto lg:overflow-visible whitespace-nowrap">
		<li class="hover:bg-gray-100 flex-shrink-0 lg:block">
			<LocalizedLink to="/account/order/list" class="block lg:px-2 px-5 py-2">
				{{ $t('Orders') }}
			</LocalizedLink>
		</li>

		<li class="hover:bg-gray-100 flex-shrink-0 lg:block">
			<LocalizedLink to="/account/profile" class="block lg:px-2 px-5 py-2">
				{{ $t('Profile') }}
			</LocalizedLink>
		</li>

		<li class="hover:bg-gray-100 flex-shrink-0 lg:block">
			<LocalizedLink to="/account/address-book" class="block lg:px-2 px-5 py-2">
				{{ $t('Address Book') }}
			</LocalizedLink>
		</li>

		<li class="hover:bg-gray-100 flex-shrink-0 lg:block">
			<LocalizedLink to="/account/password" class="block lg:px-2 px-5 py-2">
				{{ $t('Update Password') }}
			</LocalizedLink>
		</li>

		<!-- Plugin hooks -->
		<template v-for="(component, index) in $pluginStorefrontHooks['account_navigation']" :key="index">
			<component :is="component"></component>
		</template>

		<li class="hover:bg-gray-100 flex-shrink-0 lg:block">
			<LocalizedLink to="/logout" class="block lg:px-2 px-5 py-2">
				{{ $t('Logout') }}
			</LocalizedLink>
		</li>
	</ul>

</template>