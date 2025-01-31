<template>
    <div class="flex h-screen items-center justify-center p-4">
        <!--Video preview-->
        <div v-if="file && isVideo" class="mb-4">
            <!--Video preview-->
            <Video
                :file="file"
                class="mx-auto my-10 w-full self-center rounded-lg shadow-xl lg:max-w-xl xl:max-w-3xl 2xl:max-w-5xl"
            />
        </div>
    </div>
</template>

<script>
import Video from '../components/FilePreview/Media/Video'
import axios from 'axios'

export default {
    name: 'SharedSingleItem',
    components: {
        Video,
    },
    computed: {
        isVideo() {
            // Убедимся, что файл существует и его тип — видео
            return this.file && this.file.data.type === 'video'
        },
    },
    data() {
        return {
            file: undefined, // Загруженные данные о файле
        }
    },
    mounted() {
        // Загружаем файл с сервера по текущему маршруту
        axios
            .get(`/api/sharing/file/${this.$router.currentRoute.params.token}`)
            .then((response) => {
                this.file = response.data // Сохраняем файл в data
            })
            .catch((error) => {
                if (error.response && error.response.status === 403) {
                    this.$router.push({ name: 'SharedAuthentication' })
                }
            })
    },
}
</script>
