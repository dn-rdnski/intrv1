PROGRAM="audit-daemon"

$(PROGRAM): $(shell find . -type f -name '*.go')
	CGO_ENABLED=0 go build -o $(PROGRAM) cmd/main.go