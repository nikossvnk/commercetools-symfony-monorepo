<?php
/**
 */

namespace Commercetools\Symfony\CatalogBundle\Model;

use Commercetools\Core\Model\Product\Search\Facet;
use Commercetools\Core\Model\Product\Search\Filter;
use Commercetools\Core\Model\Product\Search\FilterSubtree;
use Commercetools\Core\Model\Product\Search\FilterSubtreeCollection;
use Commercetools\Core\Request\Products\ProductProjectionSearchRequest;
use GuzzleHttp\Psr7;
use Psr\Http\Message\UriInterface;

class Search
{
    /**
     * @var FacetConfig[]
     */
    private $facetConfigs = [];
    private $paramFacets = [];

    public function __construct(array $facetConfigs)
    {
        foreach ($facetConfigs as $name => $config) {
            $facetConfig = new FacetConfig(
                $name,
                $config['paramName'],
                $config['field'],
                $config['facetField'],
                $config['filterField'],
                $config['alias']
            );

            if (isset($config['type'])) {
                $facetConfig->setType($config['type']);
            }
            if (isset($config['multiSelect'])) {
                $facetConfig->setMultiSelect($config['multiSelect']);
            }
            if (isset($config['hierarchical'])) {
                $facetConfig->setHierarchical($config['hierarchical']);
            }
            if (isset($config['display'])) {
                $facetConfig->setDisplay($config['display']);
            }
            if (isset($config['ranges'])) {
                $facetConfig->setRanges($config['ranges']);
            }
            $this->paramFacets[$facetConfig->getParamName()] = $name;
            $this->facetConfigs[$name] = $facetConfig;
        }
    }

    public function getFacetConfigs()
    {
        return $this->facetConfigs;
    }

    public function getSelectedValues(UriInterface $uri)
    {
        $queryParams = Psr7\parse_query($uri->getQuery());

        $selectedValues = [];
        foreach ($queryParams as $paramName => $params) {
            if (!isset($this->paramFacets[$paramName])) {
                continue;
            }
            $facetName = $this->paramFacets[$paramName];
            $selectedValues[$facetName] = $params;
        }

        return $selectedValues;
    }

    public function addFacets(ProductProjectionSearchRequest $request, $selectedValues = [], array $showFacets = null)
    {
        if (is_null($showFacets)) {
            $showFacets = array_keys($this->facetConfigs);
        }
        foreach ($showFacets as $facetName) {
            $facetConfig = $this->facetConfigs[$facetName];
            $facet = Facet::ofName($facetConfig->getFacetField())->setAlias($facetConfig->getAlias());
            if ($facetConfig->getType() == FacetConfig::TYPE_RANGE) {
                $facet->setValue($facetConfig->getRanges());
            }
            $request->addFacet($facet);
            
            if (isset($selectedValues[$facetName])) {
                $filter = Filter::ofName($facetConfig->getFilterField());

                if ($facetConfig->getType() == FacetConfig::TYPE_RANGE) {
                    $filter->setValue($facetConfig->createRanges($selectedValues[$facetName]));
                } elseif (!$facetConfig->isHierarchical()) {
                    $filter->setValue($selectedValues[$facetName]);
                } else {
                    $subtree = FilterSubtreeCollection::of();
                    if (!is_array($selectedValues[$facetName])) {
                        $selectedValues[$facetName] = [$selectedValues[$facetName]];
                    }
                    foreach ($selectedValues[$facetName] as $value) {
                        $subtree->add(FilterSubtree::ofId($value));
                    }
                    $filter->setValue($subtree);
                }
                if ($facetConfig->isMultiSelect()) {
                    $request->addFilter($filter);
                    $request->addFilterFacets($filter);
                } else {
                    $request->addFilterQuery($filter);
                }
            }
        }

        return $request;
    }
}
