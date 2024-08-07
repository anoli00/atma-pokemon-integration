<?php

declare(strict_types=1);

namespace Atma\PokemonIntegration\Model;

use Atma\PokemonIntegration\Api\PokemonDataProviderInterface;
use Atma\PokemonIntegration\Model\ErrorMessage\HandlerInterface as ErrorMessageHandler;
use Exception;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterface;

class AssignExtensionAttributeToProduct
{
    public function __construct(
        private readonly PokemonDataProviderInterface $pokemonDataProvider,
        private readonly ProductExtensionFactory $productExtensionFactory,
        private readonly ErrorMessageHandler $errorMessageHandler
    ) {
    }

    public function execute(ProductInterface $product): void
    {
        $productExtension = $product->getExtensionAttributes();

        if ($productExtension?->getPokemon()) {
            return;
        }

        $pokemonName = $product->getCustomAttribute('pokemon_name')?->getValue();
        if (!$pokemonName) {
            return;
        }

        try {
            $pokemon = $this->pokemonDataProvider->getPokemonData($pokemonName);

            if (!$pokemon) {
                return;
            }

            if ($productExtension === null) {
                $productExtension = $this->productExtensionFactory->create();
            }

            $productExtension->setPokemon($pokemon);
            $product->setExtensionAttributes($productExtension);
        } catch (Exception $e) {
            $this->errorMessageHandler->handle($e);
        }
    }
}
