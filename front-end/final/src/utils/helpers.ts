import { BASE_URL } from "../services/api/http";

export const getFotoUrl = (img: string | null | undefined) => {
  if (!img) return '/images/faces/9.jpg';
  const normalizedImg = img.replace(/\\/g, '/');
  
  // Karena backend menyediakan endpoint Route::get('/foto/{filename}') di api.php
  // Dan karena nama img yang tersimpan adalah 'foto/namafile.jpg',
  // maka BASE_URL + '/' + normalizedImg akan menjadi: http://.../api/foto/namafile.jpg
  return `${BASE_URL}/${normalizedImg}`;
};
