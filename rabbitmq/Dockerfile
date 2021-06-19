FROM rabbitmq:3-management-alpine

# Create a group and user
RUN addgroup -S appgroup && adduser -S appuser -G appgroup --uid 1000

# Tell docker that all future commands should run as the appuser user
USER appuser
