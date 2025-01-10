<template>
    <video
        v-if="videoSrc"
        :src="videoSrc"
        class="video"
        :class="{ 'file-shadow': !isMobile }"
        controlsList="nodownload"
        disablePictureInPicture
        playsinline
        controls
    />
</template>

<script>
export default {
    name: 'Video',
    props: {
        file: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            videoSrc: null,
            isMobile: false
        };
    },
    methods: {
        async loadVideo() {
            try {
                const response = await fetch(this.file.data.attributes.file_url, {
                    method: 'GET',
                    credentials: 'include' // Если требуется отправлять куки
                });

                if (!response.ok) {
                    throw new Error('Ошибка при загрузке видео');
                }

                const blob = await response.blob();
                this.videoSrc = URL.createObjectURL(blob);
            } catch (error) {
                console.error('Ошибка при загрузке видео:', error);
            }
        },
        checkMobile() {
            this.isMobile = /Mobi|Android/i.test(navigator.userAgent);
        }
    },
    mounted() {
        this.checkMobile();
        this.loadVideo();
    },
    beforeUnmount() { // Используется в Vue 3
        if (this.videoSrc) {
            URL.revokeObjectURL(this.videoSrc);
        }
    },
    beforeDestroy() { // Используется в Vue 2
        if (this.videoSrc) {
            URL.revokeObjectURL(this.videoSrc);
        }
    }
};
</script>

<style scoped>
.video {
    width: 100%;
    height: auto;
}

.file-shadow {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
</style>
