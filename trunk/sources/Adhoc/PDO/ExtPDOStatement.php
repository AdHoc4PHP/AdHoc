<?php

namespace Adhoc\PDO;

/**
 * Description of ExtPDOStatement
 *
 * @author prometheus
 */
class ExtPDOStatement extends \Adhoc\PDOStatement
{
	public function fetchAll($mode=ExtPDO::FETCH_EXT_ARR)
	{
		$result = NULL;

		switch ($mode)
		{
			case ExtPDO::FETCH_EXT_ARR:
			{
				$result = array('success'=>FALSE);
				try
				{
					$result['success'] = TRUE;
					$result['data'] = parent::fetchAll(\PDO::FETCH_ASSOC);
					$result['totalCount'] = count($result['data']);
				}
				catch (PDOException $e)
				{
					$result['error'] = $e->getMessage();
				}
				break;
			}
			case ExtPDO::FETCH_EXT_OBJ:
			{
				$result = new stdClass();
				$result->success = FALSE;
				try
				{
					$result->success = TRUE;
					$result->data = parent::fetchAll(\PDO::FETCH_OBJ);
					$result->totalCount = count($result->data);
				}
				catch (PDOException $e)
				{
					$result->error = $e->getMessage();
				}
				break;
			}
			case ExtPDO::FETCH_EXT_JSON:
			{
				$data = array('success'=>FALSE);
				try
				{
					$data['success'] = TRUE;
					$data['data'] = parent::fetchAll(\PDO::FETCH_ASSOC);
					$data['totalCount'] = count($result['data']);
				}
				catch (PDOException $e)
				{
					$data['error'] = $e->getMessage();
				}
				$result = json_encode($data);

				break;
			}
			default:
			{
				$result = parent::fetchAll($mode);
			}
		}

		return $result;
	}
}
