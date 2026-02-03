FROM mcr.microsoft.com/devcontainers/php:0-7.4

# Ensure common utilities and compatibility libraries are present
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        ca-certificates \
    && rm -rf /var/lib/apt/lists/*

USER vscode
