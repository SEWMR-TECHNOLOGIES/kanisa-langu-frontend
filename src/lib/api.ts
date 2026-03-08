const API_BASE_URL = "https://kanisalangu.sewmrtechnologies.com/api";

interface ApiOptions {
  method?: string;
  body?: Record<string, unknown> | FormData;
  headers?: Record<string, string>;
}

export async function apiRequest<T>(endpoint: string, options: ApiOptions = {}): Promise<T> {
  const { method = "GET", body, headers = {} } = options;

  const config: RequestInit = {
    method,
    credentials: "include",
    headers: {
      ...headers,
    },
  };

  if (body) {
    if (body instanceof FormData) {
      config.body = body;
    } else {
      config.headers = { ...config.headers, "Content-Type": "application/json" };
      config.body = JSON.stringify(body);
    }
  }

  const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
  
  if (!response.ok) {
    throw new Error(`API Error: ${response.status}`);
  }

  return response.json();
}

export { API_BASE_URL };
