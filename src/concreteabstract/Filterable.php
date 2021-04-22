<?php

namespace Aweklin\Paystack\ConcreteAbstract;

use Aweklin\Paystack\Abstracts\{IRequest, IResponse};
use Aweklin\Paystack\Concrete\Response;
use Aweklin\Paystack\ConcreteAbstract\SearchParameter;
use Aweklin\Paystack\Exceptions\EmptyValueException;

/**
 * The base class for filtering transactions, refunds, etc against the Paystack API.
 */
abstract class Filterable {

    /**
     * Performs a transaction search based on the parameters specified.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * @param string $customParams Custom parameters to filter transactions with.
     * 
     * @return IResponse
     */
    protected abstract function search(int $pageNumber = 1, int $pageSize = 50, string $customParams = '') : IResponse;

    /**
     * If the given pageNumber and/or pageSize param is less than 1, the default is used.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return void
     */
    protected function normalizePagingParams(int &$pageNumber, int &$pageSize) : void {
        if ($pageNumber < 1)
            $pageNumber = 1;
        if ($pageSize < 1)
            $pageSize = 50;
    }

    /**
     * Returns all transactions carried out from inception till date.
     * 
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public function all(int $pageNumber = 1, int $pageSize = 50) : IResponse {
        return $this->search($pageNumber, $pageSize);
    }

    /**
     * Filters and returns all transactions carried out between two dates.
     * 
     * @param string $startDate A timestamp from which to start listing transaction e.g. 2016-09-24T00:00:05.000Z, 2016-09-21.
     * @param string $endDate A timestamp at which to stop listing transaction e.g. 2016-09-24T00:00:05.000Z, 2016-09-21.
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public function getByDates(string $startDate, string $endDate, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        $transactionParameter = new SearchParameter();
        $transactionParameter
            ->setStartDate($startDate)
            ->setEndDate($endDate);
        
        $filterResult = $this->filter($transactionParameter, $pageNumber, $pageSize);

        unset($transactionParameter);

        return $filterResult;
    }

    /**
     * Filters transactions based on custom filter parameter(s) specified.
     * 
     * @param SearchParameter $searchParameter Specifies the parameters you want to use for the filter operation.
     * @param int $pageNumber Specify exactly what page you want to retrieve. If not specify we use a default value of 1.
     * @param int $pageSize Specify how many records you want to retrieve per page. If not specify we use a default value of 50.
     * 
     * @return IResponse
     */
    public function filter(SearchParameter $searchParameter, int $pageNumber = 1, int $pageSize = 50) : IResponse {
        try {
            if (!$searchParameter)
                return new Response(true, 'Search parameter cannot be null.');
            
            $queryParams = '';

            foreach($searchParameter->get() as $key => $value) {
                $queryParams .= "&{$key}={$value}";
            }

            $this->normalizePagingParams($pageNumber, $pageSize);

            return $this->search($pageNumber, $pageSize, $queryParams);
        } catch (EmptyValueException $e) {
            return new Response(true, $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            return new Response(true, $e->getMessage());
        } catch (\Exception $e) {
            return new Response(true, $e->getMessage());
        }
    }

}