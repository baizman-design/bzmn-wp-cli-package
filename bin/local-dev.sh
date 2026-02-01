#!/usr/bin/env sh

IMAGE_NAME="bzmn-wp-cli-package-image"
CONTAINER_NAME="bzmn-wp-cli-package-container"
DOCKERFILE="../Dockerfile"

DOCKER="$(which docker)"

# build the image and start the container
echo "building Docker image \"${IMAGE_NAME}\" and starting the container \"${CONTAINER_NAME}\"..."
echo "${DOCKER} build -t ${IMAGE_NAME} . -f ${DOCKERFILE}"
${DOCKER} build -t ${IMAGE_NAME} . -f ${DOCKERFILE}
echo "${DOCKER} run --name \"${CONTAINER_NAME}\" -i -t ${IMAGE_NAME}"
${DOCKER} run --name "${CONTAINER_NAME}" -i -t ${IMAGE_NAME}

# to copy a file from the host to the container while it's running:
# docker cp file.txt $(docker ps | grep bzmn-wp-cli-package-image | awk '{print$1}'):/root
