import type { AxiosRequestConfig } from "axios";
import http from "./http";

export async function apiGet(
  url: string,
  params?: Record<string, any>,
) {
  try {
    const response = await http.get(url, {
      params,
// 🔥 penting
    });

    return {
            success : true,
            data : response.data
        } 
  } catch (error: any) {
    if (error.response?.status === 401) {
        return { success: false, error: 'Unauthorized' };
    }
    return { success: false, error };
  }
}
export async function apiPdf(
  url: string,
  params?: Record<string, any>,
  config?: AxiosRequestConfig
) {
  try {
    const response = await http.get(url, {
      params,
      responseType: "blob",
      ...config, // 🔥 penting
    });

    return response;
  } catch (error) {
    throw error; // 🔥 biar catch di pemanggil
  }
}



export async function apiPost(url: string, params?:Object) {
    try {
        const response = await http.post(url, params);
        return {
            success : true,
            data : response.data
        }
    } catch (error: any) {
        if (error.response?.status === 401) {
            return { success: false, error: 'Unauthorized' };
        }
        return { success: false, error };
    }
}

export async function apiPut(url: string, params?:Object) {
    try {
        const response = await http.put(url, params);
        return {
            success : true,
            data : response.data
        }
    } catch (error) {
        return { success: false, error };
    }
}

export async function apiDelete(url: string, params?:Object) {
    try {
        const response = await http.delete(url, {params});
        return {
            success : true,
            data : response.data
        }
    } catch (error) {
        return { success: false, error };
    }
}