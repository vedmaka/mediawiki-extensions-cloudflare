FROM mcr.microsoft.com/devcontainers/php:0-8.1

# Import Yarn GPG key using modern approach
RUN set -eux; \
    mkdir -p /etc/apt/keyrings; \
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor > /etc/apt/keyrings/yarn-archive-keyring.gpg || true

# Ensure common utilities and compatibility libraries are present
RUN apt-get update --fix-missing \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        ca-certificates \
    && rm -rf /var/lib/apt/lists/*

USER vscode
