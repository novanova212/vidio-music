// Tipe data yang cocok dengan response JSON dari backend Laravel

export interface Video {
  id: number
  title: string
  slug: string
  description: string | null
  mime_type: string
  file_size: number
  duration: number | null
  views: number
  downloads: number
  stream_url: string
  download_url: string
  thumbnail_url: string | null
}

export interface Song {
  id: number
  title: string
  slug: string
  artist: string | null
  album: string | null
  mime_type: string
  file_size: number
  duration: number | null
  plays: number
  downloads: number
  stream_url: string
  download_url: string
  cover_url: string | null
}

export interface Paginated<T> {
  data: T[]
  current_page: number
  last_page: number
  total: number
}