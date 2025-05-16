# Stage 1: Build and install dependencies
FROM --platform=linux/amd64 node:18-alpine AS builder

WORKDIR /usr/src/app

# Copy package files first for better layer caching
COPY package*.json ./
# Install production dependencies
RUN npm install --only=production

# Copy all source files
COPY . .

# Stage 2: Create optimized production image
FROM --platform=linux/amd64 node:18-alpine

WORKDIR /usr/src/app

# Copy production node_modules from builder
COPY --from=builder /usr/src/app/node_modules ./node_modules

# Copy application files
COPY --from=builder /usr/src/app/server.js ./
COPY --from=builder /usr/src/app/package*.json ./

# Create non-root user and set permissions
RUN addgroup -g 1001 nodejs && \
    adduser -S -u 1001 -G nodejs nodejs && \
    chown -R nodejs:nodejs /usr/src/app

USER nodejs

# Use exec form for proper signal handling
ENTRYPOINT ["node"]

# Use environment variables for configuration
CMD ["server.js"]

EXPOSE 3000