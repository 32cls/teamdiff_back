FROM rust:slim-bullseye AS builder
WORKDIR /usr/src/myapp
COPY . .
RUN apt-get update && apt-get install -y libpq-dev libpq5 
RUN cargo install --path .

FROM debian:bullseye-slim
RUN apt-get update && apt-get install -y libpq-dev libpq5 
COPY --from=builder /usr/local/cargo/bin/teamdiff_back /usr/local/bin/teamdiff_back
CMD ["teamdiff_back"]