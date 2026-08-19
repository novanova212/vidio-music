import axios from 'axios'
import type { Paginated, Song, Video } from '../types/media'

// Base URL API Laravel.
// - Development: Vite mem-proxy '/api' ke backend lokal (lihat vite.config.ts),
//   jadi tanpa VITE_API_URL, path relatif '/api' sudah cukup.
// - Production (Vercel): frontend & backend beda domain, jadi WAJIB set
//   env VITE_API_URL di Vercel, contoh: https://nama-service.up.railway.app/api
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
})

export const mediaApi = {
  getVideos: (page = 1) =>
    api.get<Paginated<Video>>('/videos', { params: { page } }).then((r) => r.data),

  getVideo: (slug: string) =>
    api.get<Video>(`/videos/${slug}`).then((r) => r.data),

  getSongs: (page = 1) =>
    api.get<Paginated<Song>>('/songs', { params: { page } }).then((r) => r.data),

  getSong: (slug: string) =>
    api.get<Song>(`/songs/${slug}`).then((r) => r.data),
}

export default api