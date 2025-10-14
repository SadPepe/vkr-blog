<template>
   <div class="flex flex-col justify-center items-center">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden max-w-lg w-full">
        <img src="https://images.unsplash.com/photo-1454496522488-7a8e488e8606" alt="Mountain" class="m-[0px] w-full h-64 object-cover">
        <div class="px-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 mt-4">Beautiful Mountain View</h2>
            <p class="text-gray-700 leading-tight mb-4">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam eu sapien porttitor, blandit velit ac,
                vehicula elit. Nunc et ex at turpis rutrum viverra.
            </p>
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Avatar" class="w-8 h-8 rounded-full mr-2 object-cover">
                    <span class="text-gray-800 font-semibold">John Doe</span>
                </div>
                <span class="text-gray-600">2 hours ago</span>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>


defineProps({
  
});
</script>