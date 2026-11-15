# Stage 1: Build React Application
FROM node:20-alpine AS build

WORKDIR /app

# Copy package manifests
COPY package*.json ./

# Install dependencies cleanly
RUN npm ci --legacy-peer-deps || npm install

# Copy source files
COPY . .

# Build production bundle
ENV NODE_ENV=production
RUN npm run build

# Stage 2: Production Nginx Server
FROM nginx:alpine

# Copy built artifacts from builder stage
COPY --from=build /app/build /usr/share/nginx/html

# Custom Nginx config to support client-side routing (SPA fallback)
RUN echo 'server { \
    listen 3000; \
    location / { \
        root /usr/share/nginx/html; \
        index index.html index.htm; \
        try_files $uri $uri/ /index.html; \
    } \
}' > /etc/nginx/conf.d/default.conf

EXPOSE 3000

CMD ["nginx", "-g", "daemon off;"]
