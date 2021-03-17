<template>
    <div>
        <l-map
            ref="mymap"
            :zoom="zoom"
            :center="center"
            style="height: 500px; width: 100%"
            @click="addMarker">
            <l-tile-layer :url="url" :attribution="attribution"/>
            <l-marker :lat-lng="marker !== null ? marker : null" :key="marker.lat">
            </l-marker>
        </l-map>
    </div>
</template>

<script>
import {latLng} from "leaflet";
import {LIcon, LMap, LMarker, LTileLayer} from "vue2-leaflet";
import {EventBus} from "../../main";

export default {
    name: "CustomPath",
    components: {
        LMap,
        LTileLayer,
        LMarker,
        LIcon
    },
    data() {
        return {
            zoom: 13,
            center: latLng(42.443606936580295, 19.26975388778374),
            url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
            marker: latLng(42.443606936580295, 19.26975388778374)
        };
    },
    methods: {
        onResize() {
            this.$refs.map.mapObject.invalidateSize();
        },
        addMarker(event) {
            this.marker = latLng(event.latlng);
            EventBus.$emit('marker', this.marker);
        }
    },
    mounted() {

    }
};
</script>
