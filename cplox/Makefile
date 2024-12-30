CXX = g++
CXXFLAGS = -Wall -std=c++17

SRCDIR = .
BUILDDIR = build

SRCS = $(wildcard $(SRCDIR)/*.cpp)
OBJS = $(SRCS:%.cpp=$(BUILDDIR)/%.o)
TARGET = $(BUILDDIR)/cplox

all: $(BUILDDIR) $(TARGET)

$(BUILDDIR):
	mkdir -p $(BUILDDIR)

$(TARGET): $(OBJS)
	$(CXX) $(OBJS) -o $(TARGET)

$(BUILDDIR)/%.o: $(SRCDIR)/%.cpp
	$(CXX) $(CXXFLAGS) -c $< -o $@

clean:
	rm -rf $(BUILDDIR)/*

.PHONY: all clean
