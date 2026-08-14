#!/usr/bin/env bash

# find all versions - excludes non vX.X.X
# find all stable, unstable, beta, alpha
# find latest version
# find previous version


function print-usage() {
    echo "Usage: $0 <any|stable|unstable|beta|alpha|rc> [--(any|a) | --(latest|l) | --(previous|p) | --(limit|n) n] [--output-strict-semver]" 1>&2
    exit 1
}

version_type=$1
limit=unlimited
outputStrictSemver=false

# remove required arg so that we may process options bellow
shift

if [[ -z "$version_type" ]] || ! [[ "$version_type" =~ ^(any|stable|unstable|beta|alpha|rc)$ ]]; then
    print-usage
fi


while [[ $# -gt 0 ]]; do
    case "$1" in
        --any|-a)
            limit=unlimited
            shift
        ;;
        --latest|-l)
            limit=latest
            shift
        ;;
        --previous|-p)
            limit=previous
            shift
        ;;
        --limit|-n)
            limit=number
            limitAmount="$2"

            [[ -z $limitAmount ]] && print-usage
            
            shift 2
        ;;
        --output-strict-semver)
            outputStrictSemver=true
            shift
        ;;
        *)
            print-usage
        ;;
    esac
done

# defaults to any
pattern="^v[0-9]+\.[0-9]+\.[0-9]+"

case $version_type in
    stable)
        pattern+="$"
    ;;
    unstable)
        pattern+="-."
    ;;
    beta|alpha|rc)
        pattern+="-($version_type)"
    ;;
esac



fetch-tags() {
    (git tag --sort=-v:refname | grep -E "$pattern" || exit 1) | while read -r version || [[ -n "$version" ]]; do
        if $outputStrictSemver && [[ "$version" =~ ^v ]]; then
            version="${version:1}"
        fi
        echo "$version"
    done;
}

case $limit in
    unlimited)
        fetch-tags
    ;;
    latest)
        fetch-tags | head -n 1
    ;;
    previous)
        if [[ $(fetch-tags | wc -l) -lt 2 ]]; then
            exit 1
        fi
        fetch-tags | head -n 2 | tail -n 1
    ;;
    number)
        fetch-tags | head -n "$limitAmount"
    ;;
esac


